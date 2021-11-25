import pyttsx3
import datetime 
import speech_recognition as sr
import webbrowser as wb
import os


friday=pyttsx3.init()
voices = friday.getProperty('voices')
# vi_voice_id = "HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Speech\Voices\Tokens\MSTTS_V110_viVN_An";
voice_id = "en"

# friday.setProperty('voice', voice_id) 
friday.setProperty('voice',voices[2].id) 
# friday.setProperty('rate', 150) 


def speak(audio):
    print('F.R.I.D.A.Y: ' + audio)
    friday.say(audio)
    friday.runAndWait()
   
    
def time():
    Time=datetime.datetime.now().strftime("%I:%M:%p") 
    speak("It is")
    speak(Time)

def welcome():
        #Chao hoi
        hour=datetime.datetime.now().hour
        if hour >= 6 and hour<12:
            speak("Good morning")
        elif hour>=12 and hour<18:
            speak("Good afternoon")
        elif hour>=18 and hour<24:
            speak("Good night")
        speak("Can i help you") 


def command():
    c=sr.Recognizer()
    with sr.Microphone() as source:
        c.pause_threshold=2
        audio=c.listen(source)
    try:
        query = c.recognize_google(audio,language='en-US')
        print("BOSS: "+query)
    except sr.UnknownValueError:
        query = ""
    return query

if __name__  =="__main__":
    welcome()

    while True:
        query=command().lower()
        #All the command will store in lower case for easy recognition
        if "hello" in query:
            speak("yeah, hello")
        elif "google" in query:
            speak("What should I search,boss")
            search=command().lower()
            url = f"https://google.com/search?q={search}"
            wb.get().open(url)
            speak(f'Here is your {search} on google')
        
        elif "youtube" in query:
            speak("What should I search,boss")
            search=command().lower()
            url = f"https://youtube.com/search?q={search}"
            wb.get().open(url)
            speak(f'Here is your {search} on youtube')

        elif "quit" in query:
            speak("Friday is off. Goodbye boss")
            quit()
        elif "music" == query:
            music =r"D:\MUSIC\Guitar\Andiez – Tình Yêu Của Anh.mp3"
            os.startfile(music)
        elif "stop music" == query:
            music =r"D:\MUSIC\Guitar\Andiez – Tình Yêu Của Anh.mp3"
            os.system('D:\MUSIC\Guitar\Andiez – Tình Yêu Của Anh.mp3')
        elif 'time' in query:
            time()
        else:
            speak('You can repeat ? Boss')