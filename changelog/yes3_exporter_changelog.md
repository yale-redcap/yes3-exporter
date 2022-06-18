
# YES3 Exporter Change Log

**Version 0.8.1 June 18, 2022**

1. Major README edits and reorganization
2. Export log browser date range selectors no longer updated based on observed range
3. Bug fix: Log record export failed
4. Export log browser now displays the row count
5. Export log browser UI tweaked for better responsiveness

**Version 0.8.0 June 4, 2022**

1. Designers now accorded permissions on all forms, regardless of form export and view permissions
2. Major CSS cleanup
3. Option to activate beta features removed
4. Exporter Main UI overhauled for efficiency, clarity and to maximize the amount of information displayed:
   * UI transformed from a single form with an embedded table having input fields for each item row (form/field name, event) to a list that invokes a single item editor
   * Ordering convention of item event, item object adopted throughout UI
   * Form titles (Followup MMSE) displayed instead of form names (followup_mmse).
   * Field labels are displayed as well as field names
5. Numerous UI tweaks based on user testing

**Version 0.7.5 May 25, 2022**

1. Bug fix: The selection criterion value "list" syntax failed with no records selected.
2. Bug fix: The selection criterion expression permitted a "=>" operator instead of ">=".
3. Added "<>" to the set of allowed operators for the selection criterion.
4. The selection criterion filter will perform numeric comparisons if the operand is numeric.
5. UI: 'labels always sanitized' note added to editor.
6. UI: The download dialog will now display a note about user data downloads being disabled, where appropriate.
7. UI: A detailed help popup is available from the form insertion panel.
8. Bug fix: It was possible to create duplicate export names in the settings editor.
9. When a backup is loaded by the wayback, a message is displayed admonishing the user to save it in order to accept the selection.
10. Confirmation is now required if you attempt to select another export while there are unsaved changes.
11. Bug fix: Invalid REDCap field names were not always handled properly after change.
12. YES3 logo added to the event preefix and log plugins.
13. Bug fix: Action icons embedded in help popups were assigned click handlers.

**Version 0.7.4 May 23, 2022**

1. Cron manager 'yes3_exporter_cron' redesigned. Now scheduled/reported in system settings instead of project settings. More consistent exception handling for cron tasks.
2. New housekeeping function 'hk_generations' to cull old export specification backups.

**Version 0.7.3 May 18, 2022**

1. Bug fix: Action icons that appeared to be disabled would under certain circumstances respond to clicks.
2. Bug fix: Opening an export spec for a repeating layout would crash if the user was denied view or export rights on any form in the project.
3. Debug help: Any Javascript error generated on one of our dev or staging hosts will now produce an onscreen report, reset the curser and shut down any modal dialogs.
4. Bug fix: the count of data values written was incorrect in the logs (double counted).
5. New plugin for displaying module markdown files, motivated by Parsedown apparently not supporting GitHub-flavored TOC tags. Uses GitHub CSS for light and dark themes, and has a sidebar TOC for navigation. Sidebar vanishes at the Bootstrap 'small' breakpoint. A vague plan is afoot to move all YES3 module documentation to a Docusaurus site.

**Version 0.7.2 May 14, 2022**

1. Yes3::ellipsis() replaced by Yes3::truncate in all metadata contexts (ellipses taking up too much space when maxlen is small)
2. Label sanitation function now strips all html tags, instead of just the 'dangerous' tags.
3. Bug fix: UTF8 characters not rendering properly in MS Excel and other apps, because of missing byte order mark (BOM)
4. Bug fix: it was possible, by blanking already-saved fields, to save an item with form or field name blank
5. Bug fix: bulk insertion was not inserting above the selected field. Fixed to act consistently with the 'paste' function, if a single field is selected when the bulk insert is activated.
6. Modal 'busy' message broadcast with 'wait' cursor style, for time-consuming AJAX processes (loading spec, saving spec, exporting data)
7. Export to host and download functions separated, with new icons.
8. Deprecated: The 'export target' setting is deprecated and no longer exposed on the UI.
9. New EM setting: if filesystem exports are enabled, user data downloads can be prevented. Data dictionary downloads are allowed.
10. Tailored and detailed non-modal help popup for selection criterion value
11. Bug fix: several issues with vertical and repeating export layouts addressed: (1) event selection was inoperable (hangover from original version that did not allow event selections for these layouts); (2) no data exported for repeat layouts; (3) column orders were not the same between data dictionary and exported data; (4) repeating layouts allowed fields from multiple forms
12. Export layout now appears as text, Instead of a disabled radio control.
13. The form insertion dialog behaves slightly differently. If you select an event when "all forms" is selected, the dropdown for "all forms" becomes those forms for which the event is configured. Any insertion will be limited to those forms.
14. The editor for the repeated layout behaves as for vertical, except that steps are taken to ensure that fields can only be selected from a single repeating form. Once you select one field or form, these constraint are applied: (1) the form insert icon is disabled and (2) the autocomplete for individual fields will include fields from that form only.
15. Hovering over a completed field input will display the REDCap field label.
