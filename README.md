# Invigilator 

The Invigilator is a quizaccess plugin to capture the user's screenshot (entire display surface including tabs) to detect if the user is using unfair means during the Quiz. It will capture the screenshot automatically in every 30 seconds (configurable) and store it as a PNG image. 


This plugin will help you to capture a random screenshots when the student/user is attempting the Quiz. 
Before starting the quiz, it will ask for screenshare permission. By accepting the permission you will be able to see your screenshots and you can continue to answer the questions. It will act as a video recording service like everything is capturing so the user will don't try to do anything suspicious during the exam.


## Features
- Capture screenshot of entire screen.
- Record the shared screen as video for the whole attempt, stored as short standalone segments.
- Play a whole recording session back in the browser, segment after segment, as if it were one video.
- Can't access quiz if the user does not allow the screenshare
- Admin report and check any suspicious activity
- It will work with existing Questions Bank and Quizes
- Webservice API for external call
- Images are stored in Moodledata as a small png image
- Scheduled clean up of recordings older than the retention period
- English and Turkish language packs

## Requirements

- Moodle 4.0 or later. The plugin picks up whichever class layout the site has, so it works both
  with the pre 4.2 `quiz_access_rule_base` / `external_api` classes and with the `mod_quiz` and
  `core_external` namespaced classes introduced in Moodle 4.2.
- PHP 7.4 or later, with the GD extension for the timestamp drawn on screenshots.
- A browser that supports `getDisplayMedia` and `MediaRecorder`: current Chrome, Edge or Firefox.
  Where `MediaRecorder` is missing, screenshots keep working and only the video recording is skipped.

## Screen recording

While a student attempts the quiz, the invigilator window keeps the screen share open and does two
things at once: it grabs a screenshot every `screenshotdelay` seconds, and it records video.

The recording is not one large file. The browser restarts its recorder every `recordingsegment`
seconds, so every segment that reaches the server is a complete, playable file on its own. That
keeps browser memory flat over a long exam, uploads spread out evenly, and a crash or a closed
window only ever costs the segment that was in progress.

### Settings

Site administration -> Plugins -> Activity modules -> Quiz -> Invigilator:

| Setting | Default | What it does |
| --- | --- | --- |
| `enablerecording` | Yes | Record video in addition to the screenshots. |
| `recordingsegment` | 60 | Seconds of video per uploaded segment. |
| `recordingwidth` | 1280 | The shared screen is scaled down to at most this width. |
| `recordingframerate` | 5 | Frames per second. Five is enough to follow what a student does. |
| `recordingbitrate` | 300 | Target bitrate in kbps. |
| `recordingmaxsize` | 10 | Segments larger than this many MB are rejected. |
| `recordingretention` | 0 | Days to keep recordings. 0 keeps them until deleted by hand. |

Segments are uploaded base64 encoded through the `quizaccess_invigilator_send_recording` web
service, so the payload is about a third larger than the file. Keep `recordingmaxsize` below the
`post_max_size` and `upload_max_filesize` of the web server. With the defaults a 60 second segment
is roughly 2 MB.

Recordings older than `recordingretention` days are deleted by the
`quizaccess_invigilator\task\cleanup_recordings` scheduled task, which runs nightly.

### Watching a recording

From the quiz page, staff with the `quizaccess/invigilator:viewrecording` capability get a
**View screen recordings** button, which lists one row per recording session. Playing a session
loads the segments one after another, and the segment list on the side jumps to any point in time.
`quizaccess/invigilator:deleterecording` allows deleting a whole session.

### Capabilities

| Capability | Default roles |
| --- | --- |
| `quizaccess/invigilator:sendscreenshot` | student, manager |
| `quizaccess/invigilator:sendrecording` | student, manager |
| `quizaccess/invigilator:getscreenshot` | teacher, editingteacher, manager |
| `quizaccess/invigilator:viewrecording` | teacher, editingteacher, manager |
| `quizaccess/invigilator:viewreport` | teacher, editingteacher, manager |
| `quizaccess/invigilator:deletescreenshot` | editingteacher, manager |
| `quizaccess/invigilator:deleterecording` | editingteacher, manager |

### Privacy

Screenshots and recordings are personal data. Both are declared to the privacy API, are removed
when a user or a course module is deleted, and can be purged on a schedule with the retention
setting. Tell your students what is recorded before you switch this on.

### JavaScript build files

The AMD modules under `amd/build/` are kept in step with `amd/src/` by hand, so the plugin works
without a grunt step. If you change a module, update both copies (or run `grunt amd` and commit
the generated build files).


## Configuration

You can install this plugin from [Moodle plugins directory](https://moodle.org/plugins) or can download from [Github](https://github.com/eLearning-BS23/quizaccess_invigilator).

> After installing the plugin, you can use the plugin by following:


- Go to you quiz setting (Edit Quiz): 
- Change the *Extra restrictions on attempts* -> *Screenshot capture validation*  to **must be acknowledged before starting an attempt**
- Done!
```
  Dashboard->My courses->Your Course Name->Lesson->Quiz Name->Edit settings
```
<p>
<img width="800" alt="Settings" src="https://user-images.githubusercontent.com/19352999/126743993-26b5312d-7173-4f79-9d2f-fe2c212fc650.PNG">
</P>

> Now you can attempt your quiz like this:

<p>
<img width="800" alt="StartAttempt" src="https://user-images.githubusercontent.com/19352999/126743491-4edd9f35-257a-42d3-bc73-469ca05a1a63.PNG">
</p>

> You can check the report from Admin Site:

<p>
  <img width="800" alt="Report1" src="https://user-images.githubusercontent.com/19352999/126743566-c397792a-e303-4275-b155-2bbd657dffc8.PNG">
</p>

<p>
  <img width="800" alt="Report2" src="https://user-images.githubusercontent.com/19352999/126743603-a6be4c08-9229-4d4a-b8e5-ce7f41336bb1.PNG">
</p>

## License

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
