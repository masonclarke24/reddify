<?php

namespace App\Http\Controllers;

require('C:\Code\reddify\vendor\autoload.php');
use \App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

class VoiceController extends Controller
{
    /**
     * Display a listing of all avaliable voices
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $client = new TextToSpeechClient(['credentials' => 'C:\Code\reddify\My-Project-89725-d50377369ac9.json']);

        $voices = $client->listVoices()->getVoices();

        $result = array();
        foreach ($voices as $voice) {
            $languages = array();
            foreach ($voice->getLanguageCodes() as $language) {
                if($language != '[]')
                    $languages[] = $language;
            }
            $result[] = (object) array('name' => $voice->getName(), 'languageCodes' => $languages, 'gender' => $voice->getSsmlGender());
        }

        return $result;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Retrieve an audio sample in the selected voice with the selected text
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        // create client object
        $client = new TextToSpeechClient(['credentials' => 'C:\Code\reddify\My-Project-89725-d50377369ac9.json']);

        $gender = strtoupper($request->all()[2]) == "MALE" ? SsmlVoiceGender::MALE : SsmlVoiceGender::FEMALE; 
        $voice = (new VoiceSelectionParams())
            ->setName($request->all()[0])
            ->setLanguageCode($request->all()[1])
            ->setSsmlGender($gender);

        PostController::saveTextToSpeech($client, $request->all()[3], $voice, $request->all()[0].'.mp3');

        return $request->all()[0].'.mp3';
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
