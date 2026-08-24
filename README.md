# Invigilator 

The Invigilator is a quizaccess plugin to capture the user's screenshot (entire display surface including tabs) to detect if the user is using unfair means during the Quiz. It will capture the screenshot automatically in every 30 seconds (configurable) and store it as a PNG image. 


This plugin will help you to capture a random screenshots when the student/user is attempting the Quiz. 
Before starting the quiz, it will ask for screenshare permission. By accepting the permission you will be able to see your screenshots and you can continue to answer the questions. It will act as a video recording service like everything is capturing so the user will don't try to do anything suspicious during the exam.


## Features
- Capture the entire screen, with the interval, quality and width set by the site administrator.
- Capture the shared screen as one small image every few seconds for the whole attempt.
- Play a session back in the browser as a time lapse, with speed control and scrubbing.
- Browse every screenshot of an attempt as an album of thumbnails, and enlarge any of them with previous and next arrows.
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

While a student attempts the quiz, the shared screen is captured as a JPEG every few seconds. All
images of one attempt share a session id, and the report plays them back in order, so a whole
attempt can be watched as a time lapse. Video was tried and dropped: it costs hundreds of megabytes
per student, while a sampled attempt costs tens of megabytes and a dropped connection only ever
loses a single image.

There is one capture pipeline and one set of settings. The screenshot loop the plugin used to run
in parallel was removed in 2.2.0; the screenshots it stored in the past are still readable through
**View invigilator report**.

### Settings

Site administration -> Plugins -> Activity modules -> Quiz -> Invigilator. Values are typed in and
checked when the form is saved, so a mistyped zero is refused rather than silently corrected:

| Setting | Default | Range | What it does |
| --- | --- | --- | --- |
| Capture the screen | Yes | - | Turns the capture off without lifting the screen share requirement. |
| Seconds between screenshots | 10 | 2-600 | How often the screen is captured. |
| Screenshot quality | 60 | 10-100 | JPEG quality of each image. |
| Screenshot width (pixels) | 1280 | 320-3840 | Images are scaled down to at most this width. |
| Album thumbnail width (pixels) | 240 | 80-640 | Width of the small copy shown in the album. |
| Keep screenshots for (days) | 0 | 0-3650 | 0 keeps them until deleted by hand. |
| Maximum size of one screenshot (MB) | 2 | 1-50 | Larger images are refused. |

With the defaults one screenshot is roughly 100-150 KB, so a one hour attempt costs about 40 MB.
Doubling the interval halves that; dropping the width to 960 px roughly halves it again.

Images are uploaded base64 encoded through the `quizaccess_invigilator_send_frame` web service, so
the payload is about a third larger than the image. Keep the maximum size below the `post_max_size`
of the web server.

Screenshots older than the retention period are deleted by the
`quizaccess_invigilator\task\cleanup_recordings` scheduled task, which runs nightly.

### Starting an attempt

The preflight form walks the student through two steps, in this order:

1. **Share the screen.** Until a display surface is really being captured, the agreement checkbox
   stays disabled and carries the hint *"Share your screen first, then tick this box."*
2. **Tick the agreement box.** Only then does the start button unlock.

Stopping the share puts the form back to step one: the box is unticked and disabled again, and the
start button locks. Submitting the form anyway is blocked in the browser, and
`validate_preflight_check()` refuses the post on the server, which is the check that actually
decides: it requires `invigilator_window_surface` to be `live`, `invigilator_share_state` to be
`true`, and the checkbox to be set. Those two hidden fields are ordinary form elements written by
the browser, so like any client supplied value they keep an honest student honest rather than
defeating a determined one; the capture itself is what the report is based on.

The check runs for every new attempt and is skipped only when an existing attempt is continued.

### Watching a capture session

From the quiz page, staff with the `quizaccess/invigilator:viewrecording` capability get a
**View screen captures** button, which lists one row per session. Opening a session gives two views
of the same attempt:

- **The player** at the top steps through the screenshots at 1 to 8 per second, with a scrub bar
  and single frame stepping, so an attempt can be watched as a time lapse.
- **The album** below it shows every screenshot of the session as a thumbnail, oldest first, each
  labelled with the time it was taken. The thumbnail of the frame the player is on is highlighted.

Clicking a thumbnail, or the player frame itself, opens that screenshot at its full captured size,
and the arrows (or the left and right keys) move to the screenshot before and after it. The player
pauses while the enlarged view is open. The same lightbox is used by the older screenshot report.

Thumbnails are stored next to the screenshots in a `thumbnail` file area, roughly 10 KB each, and
are lazily loaded so an album of several hundred screenshots stays quick to open. Screenshots
captured before the album existed get their thumbnails from the nightly clean up task, and fall
back to the full sized image until then.
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
