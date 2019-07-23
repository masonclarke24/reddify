<?php



namespace App\Http\Controllers;

//require('C:\Code\reddify\vendor\autoload.php');

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

class PostController extends Controller
{
    public function createVideo(Request $request)
    {

        return shell_exec($request->all()[0]);
        if(!is_dir(getPathOf('audio')) || !file_exists(getPathOf('audio'))){
            mkdir(getPathOf('audio'));
        }
        if(!is_dir(getPathOf('images')) || !file_exists(getPathOf('images'))){
            mkdir(getPathOf('images'));
        }
        if(!is_dir(getPathOf('videos')) || !file_exists(getPathOf('videos'))){
            mkdir(getPathOf('videos'));
        }
        //clean up the directories where the video assets will be stored
        PostController::clearDirectory(getPathOf('audio'));
        PostController::clearDirectory(getPathOf('images'));
        PostController::clearDirectory(getPathOf('videos'));

        //Extract the voice info, always the first item
        $voiceInfo = $request->all()[0];
        // create client object
        $client = new TextToSpeechClient(['credentials' => getPathOf('cloudCredentials')]);

        
        // note: the voice can also be specified by name
        // names of voices can be retrieved with $client->listVoices()
        $voice = (new VoiceSelectionParams())
            ->setName($voiceInfo['name'])
            ->setLanguageCode($voiceInfo['language'])
            ->setSsmlGender($voiceInfo['gender'] == 'Male' ? SsmlVoiceGender::MALE : SsmlVoiceGender::FEMALE);


        $contentPadding = 100; //The distance from the edge of the frame each comment will be
        $contentFontSize = 33; //The font size for the comment text
        $fontRaleway = getcwd() . getPathOf('font'); //The location of the font to be used
        $screenSize = (object) [];
        $screenSize->width = 1920;
        $screenSize->height = 1080;

        //The remainder of the request is the array containing all the comments
        $request = $request->all()[1];

        $totalSentences = 0;//The total number of sentences to process. Used to calculate progress. Ths number is doubled
        //because the images are created first, with one sentence per image, and then the video clips are created - two seperate processes
        foreach($request as $r){
            $totalSentences += substr_count($r['content'], '.');
        }

        $totalSentences *= 2;
        $progress = 0;
        session(['progress' => 0]);

        Session::save();
        //extract all of the comments from the post request as an array
        foreach ($request as $key => $comment) {

            //Create an image for each sentence in the comment so extract all the sentences
            $sentences = preg_split("/[!?\.]/", $comment['content']);
            $sentenceNumber = 0; //Used fo file naming in the form of comment-{commentNumber}_{sentenceNumber}


            //echo (($sentenceNumber + 1) * ($key + 1)) / $totalSentences." : ";

            $sentences = array_filter($sentences, function ($var) {
                return strlen($var) > 0;
            });

            //Add the period back to each item as needed
            array_walk($sentences, function (&$item, $key) {

                $item = trim($item);
                //remove newline ect. These will change how the image is rendered
                $item = str_replace("\n", '', $item);
                //Check if sentence ends in '.', if not, add one
                if ($item[strlen($item) - 1] != '.')
                    $item = $item . '.';
            });

            $endOfPreviousString = (object) [];
            $endOfPreviousString->x = $contentPadding;
            $endOfPreviousString->y = $contentPadding;

            $image = PostController::createImageWithText($comment['author'], $screenSize->width, $screenSize->height, $fontRaleway);


            foreach ($sentences as $sentence) {

                PostController::saveTextToSpeech($client, $sentence, $voice, getPathOf('audio'). '/comment-' . sprintf('%02d', $key) . '_' . sprintf('%02d', $sentenceNumber) . '.mp3');

                //Get the width of the sentence to see if it will fit in the screen
                $sentenceSize = \App\Http\Controllers\PostController::calculateTextBox($contentFontSize, 0, $fontRaleway, $sentence);

                //avaliableScreenSpace is underestimated fro some reason... 5.5 seems to work
                $avaliableScreenSpace = $screenSize->width - $endOfPreviousString->x + $contentPadding * 5.5;

                //keep splitting up the sentence to get it to fit on the screen without overflowing
                do {

                    $avaliableScreenSpace = (($avaliableScreenSpace < 0) ? 0 : $avaliableScreenSpace);

                    //Take words off the end until the left portion of the sentence will fit in the avaliableScreenSpace
                    $resizedStrings = \App\Http\Controllers\PostController::resizeStrings($sentence, $avaliableScreenSpace, $contentFontSize, $fontRaleway);

                    $sentenceSize = \App\Http\Controllers\PostController::calculateTextBox($contentFontSize, 0, $fontRaleway, $resizedStrings->left);

                    //Insert the left string onto the existing line left by 76 percent, prints too far right for some reason
                    $image->text($resizedStrings->left, ($endOfPreviousString->x) * 0.76, $endOfPreviousString->y, function ($font) use ($contentFontSize, $fontRaleway) {
                        $font->file($fontRaleway);
                        $font->size($contentFontSize);
                        $font->color('#FFFFFF');
                    });


                    $endOfPreviousString->x = $endOfPreviousString->x + $sentenceSize['width'];

                    $sentence = $resizedStrings->right;

                    //The entire sentence couldn't fit on one line so make a new line
                    if (strlen($sentence) > 0) {
                        $endOfPreviousString->x = $contentPadding;
                        $endOfPreviousString->y = $endOfPreviousString->y + $contentFontSize;

                        //this comment is veeeeeery long and won't fit on this screen. save the current screen and make a new one
                        if ($screenSize->height - $endOfPreviousString->y - $contentPadding < 0) {
                            $image->save(getcwd() . '/images/comment-' . sprintf('%02d', $key) . '_' . sprintf('%02d', $sentenceNumber++) . '.png');

                            session(['progress' => ($progress++ / $totalSentences) > 1 ? 1 : ($progress / $totalSentences)]);
                            $image = PostController::createImageWithText($comment['author'], $screenSize->width, $screenSize->height, $fontRaleway);
                            //New screen so reset the vertical space
                            $endOfPreviousString->y = $contentPadding;

                            //Since this sentence was split between two screens, we need to split its audio track in two as well
                            //Delete this sentence's audio file. We are rebuilding it
                            //return getcwd() . '\audio\comment-' . sprintf('%02d', $key) . '_' . ($sentenceNumber - 1) . '.mp3';
                            unlink(getcwd() . getPathOf('audio'). '/comment-' . sprintf('%02d', $key) . '_' . sprintf('%02d', ($sentenceNumber - 1)) . '.mp3');

                            //now rebuild the audio, first with the sentence fragment on the previous screen
                            PostController::saveTextToSpeech($client, $resizedStrings->left, $voice, getcwd() . getPathOf('audio'). '/comment-' . sprintf('%02d', $key) . '_' . sprintf('%02d', $sentenceNumber - 1) . '.mp3');
                            PostController::saveTextToSpeech($client, $resizedStrings->right, $voice, getcwd() . getPathOf('audio'). '/comment-' . sprintf('%02d', $key) . '_' . sprintf('%02d', $sentenceNumber) . '.mp3');
                        }
                    }
                    $avaliableScreenSpace = $screenSize->width - $endOfPreviousString->x + $contentPadding * 5.5;
                    //Repeat as needed (multiple lines may be needed for very long strings)
                } while (strlen($sentence) > 2);

                $image->save(getcwd() . '/images/comment-' . sprintf('%02d', $key) . '_' . sprintf('%02d', $sentenceNumber) . '.png');
                $sentenceNumber++;
                session(['progress' => ($progress++ / $totalSentences) > 1 ? 1 : ($progress / $totalSentences)]);
                Session::save();
                
            }

            $client->close();
        }

        $imageFiles = scandir(getPathOf('images'));
        $audioFiles = scandir(getPathOf('audio'));

        //create multiple videos consisting of the comment and it's audio track. The file name must also be recorded for later use

        $fileNames = fopen(getPathOf('videos')."/fileNames.txt", 'a');
        file_put_contents(getPathOf('videos')."/fileNames.txt", '');
        for ($i = 2; $i < sizeof($imageFiles); $i++) {

            exec("getPathOf('ffmpeg')"." -i " . getPathOf('images') ."/" . $imageFiles[$i] . ' -i ' . getPathOf('audio') ."/" . $audioFiles[$i] . ' ' . getPathOf('videos'). "/output(" . sprintf('%03d', $i) . ').avi > output.txt');

            //Recore the name of the video in the filenames file
            $outputFileName = 'file ' . 'output(' . sprintf('%03d', $i) . ').avi' . PHP_EOL;
            fwrite($fileNames, $outputFileName);

            //update the progress
            session(['progress' => ($progress++ / $totalSentences) > 1 ? 1 : ($progress / $totalSentences)]);
            Session::save();
        }

        fclose($fileNames);

        //concattonate all the videos into one creating the final result
        exec(getPathOf('ffmpeg')." -f concat -safe 0 -i " . getPathOf('videos'). "/fileNames.txt -c copy " . getPathOf('videos'). "/" . date('d_M_Y-H_i') . '_reddify.avi');

        return "/download/" . date('d_M_Y-H_i') . '_reddify/avi';
    }

    //Resize the given string to fit the space constraint. Two strings are returned with left being the string that will fit and right being the remaining portion
    public function resizeStrings(string $str, int $spaceConstraint, int $fontSize, string $fontFile)
    {
        $left = $str; //The left portion of the sentence that will be inserted in the screen
        $right = ''; //the right portion of the original sentence. This will go on  a new line

        $sentenceSize = \App\Http\Controllers\PostController::calculateTextBox($fontSize, 0, $fontFile, $left);
        while ($spaceConstraint - $sentenceSize['width'] < 0) {
            $lastWord = strrpos($left, ' ');

            //There is only one word left; this string cannot fit in the avaliable space so move the entire string to $right
            if (!((bool)$lastWord)) 
            {
                $right = $left . ' ' . $right;
                $left = '';
            } 
            else {
                $right = substr($left, $lastWord) . $right;
                $left = substr($left, 0, $lastWord);
            }


            // echo('*Left*: ' .$sentenceSize['width'] .'*Right*: ' .$right);
            $sentenceSize = \App\Http\Controllers\PostController::calculateTextBox($fontSize, 0, $fontFile, $left);
        }


        $result = (object) [];
        $result->left = trim($left) . ' ';
        $result->right = trim($right);
        //echo('End of left string '.$result->left );
        return $result;
    }

    function calculateTextBox($font_size, $font_angle, $font_file, $text)
    {
        $box   = imagettfbbox($font_size, $font_angle, $font_file, $text);
        return array(
            'width' => abs($box[4] - $box[0]),
            'height' => abs($box[5] - $box[1]),
            'left' => $box[0],
            'top' => $box[5]
        );
    }

    function clearDirectory(string $dir)
    {
        //clean up the directories where the images audio and videos are stored
        $directory = scandir($dir);

        //$directory = array_filter($directory, function($var){return $var != '.' || $var != '..';});
        for ($i = 2; $i < sizeof($directory); $i++)
            unlink($dir . "/" . $directory[$i]);
    }

    function createImageWithText(string $text, int $width, int $height, string $textFont)
    {
        //Create a blank image (frame) to display each frction of the comment
        $image = Image::canvas($width, $height, '#000000');

        //Write the author in the top right. This will be done on every image
        $image->text($text, 20, 20, function ($font) use ($textFont) {
            $font->file($textFont);
            $font->size(15);
            $font->color('#FFFFFF');
        });

        return $image;
    }

    static function saveTextToSpeech(\Google\Cloud\TextToSpeech\V1\TextToSpeechClient  $client, string $text, \Google\Cloud\TextToSpeech\V1\VoiceSelectionParams $voice, string $fileName)
    {

        $audioConfig = (new AudioConfig())
            ->setAudioEncoding(AudioEncoding::MP3);

        $input_text = (new SynthesisInput())
            ->setText($text);

        $response = $client->synthesizeSpeech($input_text, $voice, $audioConfig);
        $audioContent = $response->getAudioContent();

        file_put_contents($fileName, $audioContent);
    }
}
