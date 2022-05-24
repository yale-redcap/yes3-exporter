![example workflow](https://github.com/yale-redcap/yes3-exporter/actions/workflows/psalm-security.yml/badge.svg)
![example workflow](https://github.com/yale-redcap/yes3-exporter/actions/workflows/psalm-static.yml/badge.svg)
![example workflow](https://github.com/yale-redcap/yes3-exporter/actions/workflows/codeql-javascript.yml/badge.svg)


# YES3 Exporter Documentation

REDCap External Module  
Version 1.00, May 2022

## Introduction

The YES3 Exporter external module compliments REDCap's reports and data export tool by providing features optimized for automating, auditing and maintaining datamarts.

## Features

-   **Flexible output spreadsheet layouts**. Longitudinal studies can take advantage of the *horizontal* layout, in which there is one row output per study subject and one column for each field and event combination. All studies can use the *vertical* layout, which will have one row per study subject and (if relevant) event combination, and one column for each field. Repeating forms can be exported using the similar *repeated* layout, which has one row per study subject, event and instance combination.
-   **Any number of export specifications may be designed and stored**.
-   **Export specifications can be rolled back**. Any specification can be restored to a prior version. By default 20 generations are retained for each export, but you can set this count to any number.
-   **Expeditiously handles large exports**. For example, in one benchmark it took 186 seconds to export 3,272 rows and 3,079 columns to a 33MB export csv file.
-   **Enhanced data dictionary, that includes metadata and data distributions(!)**. Every download or export is accompanied by a data dictionary that includes metadata that can drive external code generators. The data dictionary also includes export-specific *data distribution summaries* for each column. These include the count, range, \#nonblank, and either a frequency table or mean and variance, as appropriate to the field type (dates are treated as continuous variables). Distributional summaries may be used by datamart processes to validate data incorporated from each export. All by itself, the data dictionary can be the basis of basic study progress and data completion reports.
-   **Detailed audit for every download and export**. Daily activity summaries can be sent to a designated project email address.
-   **Can export directly to the host filesystem**. The use-case we envision for this feature is a REDCap host on which mountpoints have been configured that are directed to secure institutional fileshares. This makes it possible for exports to be written to any location deemed secure by institutional IT services. If filesystem exports are enabled for a project, data downloads can be disabled. Automated filesystem exports will be supported in the next YES3 Exporter release.
-   **Incorporates form-specific user export and access rights**. Form access rights are combined with export rights to determine which exports a user may access. Access is denied if an export specification contains fields from forms for which the user does not have export or view permission. This extra layer of protection is to further guard against unblinding. The YES3 exporter is compatible with the REDCap v12 form-specific export permission model, as well as with the early "all or nothing" model. For earlier REDCap versions, the YES3 Exporter handling of form access permissions effectively mimics the v12 form-specific exporter permissions.
-   **Dark and Light themes**. Full support for dark and light themes. Theme preference will persist between browser sessions, and will apply to future YES3 external modules. Theme switching seems to be something of an emerging standard, but we use a preference strategy (localstorage "theme" setting) that is shared by other development teams.
-   **Supports deidentified and coded datasets**. As you can for REDCap exports, you may exclude PHI, date fields and freetext fields. ID codes can be hashed and dates shifted, using the same algorithms as the REDCap data export tool.

## About YES3

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec interdum ac odio in rhoncus. Vestibulum vel purus diam. Donec sed molestie purus, vitae vulputate nibh. Vivamus pharetra diam sit amet nulla pretium blandit. Sed malesuada, nisi ut egestas egestas, velit sem sollicitudin nibh, vitae facilisis ipsum nunc at felis. Sed et malesuada turpis. Aenean gravida diam id enim sollicitudin, nec eleifend ligula gravida. Etiam pellentesque dictum mi ac consequat. Donec sem ante, sollicitudin a blandit vel, iaculis vel massa. Mauris faucibus ligula eu eros fringilla, ac rutrum diam accumsan. Phasellus eget tortor lectus. Vivamus ultricies finibus ex. Phasellus consequat nisi vel elementum vulputate. Quisque congue vestibulum est, mollis congue arcu viverra vitae. Etiam id ligula at mauris viverra lacinia.

## EM Settings

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec interdum ac odio in rhoncus. Vestibulum vel purus diam. Donec sed molestie purus, vitae vulputate nibh. Vivamus pharetra diam sit amet nulla pretium blandit. Sed malesuada, nisi ut egestas egestas, velit sem sollicitudin nibh, vitae facilisis ipsum nunc at felis. Sed et malesuada turpis. Aenean gravida diam id enim sollicitudin, nec eleifend ligula gravida. Etiam pellentesque dictum mi ac consequat. Donec sem ante, sollicitudin a blandit vel, iaculis vel massa. Mauris faucibus ligula eu eros fringilla, ac rutrum diam accumsan. Phasellus eget tortor lectus. Vivamus ultricies finibus ex. Phasellus consequat nisi vel elementum vulputate. Quisque congue vestibulum est, mollis congue arcu viverra vitae. Etiam id ligula at mauris viverra lacinia.

## Getting Started

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec interdum ac odio in rhoncus. Vestibulum vel purus diam. Donec sed molestie purus, vitae vulputate nibh. Vivamus pharetra diam sit amet nulla pretium blandit. Sed malesuada, nisi ut egestas egestas, velit sem sollicitudin nibh, vitae facilisis ipsum nunc at felis. Sed et malesuada turpis. Aenean gravida diam id enim sollicitudin, nec eleifend ligula gravida. Etiam pellentesque dictum mi ac consequat. Donec sem ante, sollicitudin a blandit vel, iaculis vel massa. Mauris faucibus ligula eu eros fringilla, ac rutrum diam accumsan. Phasellus eget tortor lectus. Vivamus ultricies finibus ex. Phasellus consequat nisi vel elementum vulputate. Quisque congue vestibulum est, mollis congue arcu viverra vitae. Etiam id ligula at mauris viverra lacinia.

## The Export Specification Editor

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec interdum ac odio in rhoncus. Vestibulum vel purus diam. Donec sed molestie purus, vitae vulputate nibh. Vivamus pharetra diam sit amet nulla pretium blandit. Sed malesuada, nisi ut egestas egestas, velit sem sollicitudin nibh, vitae facilisis ipsum nunc at felis. Sed et malesuada turpis. Aenean gravida diam id enim sollicitudin, nec eleifend ligula gravida. Etiam pellentesque dictum mi ac consequat. Donec sem ante, sollicitudin a blandit vel, iaculis vel massa. Mauris faucibus ligula eu eros fringilla, ac rutrum diam accumsan. Phasellus eget tortor lectus. Vivamus ultricies finibus ex. Phasellus consequat nisi vel elementum vulputate. Quisque congue vestibulum est, mollis congue arcu viverra vitae. Etiam id ligula at mauris viverra lacinia.
### An Image. I will make this a really long heading in order to see if the text overflow is handled properly.

![Graphical user interface Description automatically generated](media/8dabf52bfe6520e134da8184793aaec6.png)

### A Table

|   | A  | B  | C  | D  |
|---|----|----|----|----|
| 1 | a1 | b1 | c1 | d1 |
| 2 | a2 | b2 | c2 | d2 |
| 3 | a3 | b3 | c3 | d3 |
| 4 | a4 | b4 | c4 | d4 |

## The Export Data Dictionary

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec interdum ac odio in rhoncus. Vestibulum vel purus diam. Donec sed molestie purus, vitae vulputate nibh. Vivamus pharetra diam sit amet nulla pretium blandit. Sed malesuada, nisi ut egestas egestas, velit sem sollicitudin nibh, vitae facilisis ipsum nunc at felis. Sed et malesuada turpis. Aenean gravida diam id enim sollicitudin, nec eleifend ligula gravida. Etiam pellentesque dictum mi ac consequat. Donec sem ante, sollicitudin a blandit vel, iaculis vel massa. Mauris faucibus ligula eu eros fringilla, ac rutrum diam accumsan. Phasellus eget tortor lectus. Vivamus ultricies finibus ex. Phasellus consequat nisi vel elementum vulputate. Quisque congue vestibulum est, mollis congue arcu viverra vitae. Etiam id ligula at mauris viverra lacinia.

## Exporting to the Host Filesystem

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec interdum ac odio in rhoncus. Vestibulum vel purus diam. Donec sed molestie purus, vitae vulputate nibh. Vivamus pharetra diam sit amet nulla pretium blandit. Sed malesuada, nisi ut egestas egestas, velit sem sollicitudin nibh, vitae facilisis ipsum nunc at felis. Sed et malesuada turpis. Aenean gravida diam id enim sollicitudin, nec eleifend ligula gravida. Etiam pellentesque dictum mi ac consequat. Donec sem ante, sollicitudin a blandit vel, iaculis vel massa. Mauris faucibus ligula eu eros fringilla, ac rutrum diam accumsan. Phasellus eget tortor lectus. Vivamus ultricies finibus ex. Phasellus consequat nisi vel elementum vulputate. Quisque congue vestibulum est, mollis congue arcu viverra vitae. Etiam id ligula at mauris viverra lacinia.

## Export Logs

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec interdum ac odio in rhoncus. Vestibulum vel purus diam. Donec sed molestie purus, vitae vulputate nibh. Vivamus pharetra diam sit amet nulla pretium blandit. Sed malesuada, nisi ut egestas egestas, velit sem sollicitudin nibh, vitae facilisis ipsum nunc at felis. Sed et malesuada turpis. Aenean gravida diam id enim sollicitudin, nec eleifend ligula gravida. Etiam pellentesque dictum mi ac consequat. Donec sem ante, sollicitudin a blandit vel, iaculis vel massa. Mauris faucibus ligula eu eros fringilla, ac rutrum diam accumsan. Phasellus eget tortor lectus. Vivamus ultricies finibus ex. Phasellus consequat nisi vel elementum vulputate. Quisque congue vestibulum est, mollis congue arcu viverra vitae. Etiam id ligula at mauris viverra lacinia.

## What's in the Pipeline

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec interdum ac odio in rhoncus. Vestibulum vel purus diam. Donec sed molestie purus, vitae vulputate nibh. Vivamus pharetra diam sit amet nulla pretium blandit. Sed malesuada, nisi ut egestas egestas, velit sem sollicitudin nibh, vitae facilisis ipsum nunc at felis. Sed et malesuada turpis. Aenean gravida diam id enim sollicitudin, nec eleifend ligula gravida. Etiam pellentesque dictum mi ac consequat. Donec sem ante, sollicitudin a blandit vel, iaculis vel massa. Mauris faucibus ligula eu eros fringilla, ac rutrum diam accumsan. Phasellus eget tortor lectus. Vivamus ultricies finibus ex. Phasellus consequat nisi vel elementum vulputate. Quisque congue vestibulum est, mollis congue arcu viverra vitae. Etiam id ligula at mauris viverra lacinia.
