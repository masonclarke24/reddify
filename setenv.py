import subprocess

file = open(".env", "r")

filetext = file.readlines()
#remove the end of line as it is not part of the key
filetext = list(map(lambda t: t[0: len(t) - 1], filetext))

#remove all comments from the file and any empty strings
filetext = list(filter(lambda t: not t.startswith("#") and t, filetext))


#split the one line key value pair into a ke and value on the first equals sign

#filetext = list(map(lambda t: (t[0:t.index("=")],t[t.index("=") + 1: len(t)]), filetext))

#create the command text to set all the environmental variables at once
commandText = "C:\\Users\\Mason\\.ebcli-virtual-env\\executables\\eb.bat setenv "


for str in filetext:
    commandText += '"' + str + '" '
    
#put all the env values into the environment
#for kvp in filetext:
print(commandText)



