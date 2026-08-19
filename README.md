# Invigilator 

The Invigilator is a quizaccess plugin to capture the user's screenshot (entire display surface including tabs) to detect if the user is using unfair means during the Quiz. It will capture the screenshot automatically in every 30 seconds (configurable) and store it as a PNG image. 


This plugin will help you to capture a random screenshots when the student/user is attempting the Quiz. 
Before starting the quiz, it will ask for screenshare permission. By accepting the permission you will be able to see your screenshots and you can continue to answer the questions. It will act as a video recording service like everything is capturing so the user will don't try to do anything suspicious during the exam.


## Features
- Capture screenshot of entire screen.
- Sample the shared screen into a capture session: one small image every few seconds for the whole attempt.
- Play a session back in the browser as a time lapse, with speed control, scrubbing and a frame list.
- Can't access quiz if the user does not allow the screenshare
- Admin report and check any suspicious activity
- It will work with existing Questions Bank and Quizes
- Webservice API for external call
- Images are stored in Moodledata as a small png image
- Scheduled clean up of captured frames older than the retention period
- English and Turkish language packs

## Requirements

- Moodle 4.0 or later. The plugin picks up whichever class layout the site has, so it works both
  with the pre 4.2 `quiz_access_rule_base` / `external_api` classes and with the `mod_quiz` and
  `core_external` namespaced classes introduced in Moodle 4.2.
- PHP 7.4 or later, with the GD extension for the timestamp drawn on screenshots.
- A browser that supports `getDisplayMedia` and canvas image export: current Chrome, Edge or Firefox.

## Screen capture (time lapse)

While a student attempts the quiz, the invigilator window keeps the screen share open and does two
things at once: it grabs a screenshot every `screenshotdelay` seconds through the original
screenshot pipeline, and it samples the screen into a capture session.

Video of a whole exam runs to hundreds of megabytes per student, so the session is not filmed. The
screen is sampled instead: one compressed JPEG every `recordinginterval` seconds, every frame of an
attempt sharing a session id. The report plays the frames of a session back in order, which watches
like a time lapse of the attempt at a fraction of the storage. A dropped connection only ever costs
a single frame.

### Settings

Site administration -> Plugins -> Activity modules -> Quiz -> Invigilator. The interval and the
frame size are the two values worth tuning for your storage budget:

| Setting | Default | What it does |
| --- | --- | --- |
| `enablerecording` | Yes | Sample the screen in addition to the screenshots. |
| `recordinginterval` | 10 | Seconds between two captured frames. |
| `recordingwidth` | 1280 | Frames are scaled down to at most this width. |
| `recordingquality` | 60 | JPEG quality of each frame, 1 to 100. |
| `recordingmaxsize` | 2 | Frames larger than this many MB are rejected. |
| `recordingretention` | 0 | Days to keep frames. 0 keeps them until deleted by hand. |

With the defaults a frame is roughly 100-150 KB, so a one hour attempt costs about 40 MB. Doubling
the interval halves that; dropping the width to 960 px roughly halves it again.

Frames are uploaded base64 encoded through the `quizaccess_invigilator_send_frame` web service, so
the payload is about a third larger than the image. Keep `recordingmaxsize` below the
`post_max_size` of the web server.

Frames older than `recordingretention` days are deleted by the
`quizaccess_invigilator\task\cleanup_recordings` scheduled task, which runs nightly.

### Watching a capture session

From the quiz page, staff with the `quizaccess/invigilator:viewrecording` capability get a
**View screen captures** button, which lists one row per session. Opening a session gives a player
that steps through the frames at 1 to 8 frames per second, with a scrub bar, single frame stepping
and a time stamped frame list to jump anywhere in the attempt.
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

Screenshots and captured frames are personal data. Both are declared to the privacy API, are
removed when a user or a course module is deleted, and can be purged on a schedule with the
retention setting. Tell your students what is captured before you switch this on.

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
