# YES3 Exporter README

REDCap External Module  
Version 1.00, July 2022

## Important note about documentation

The YES3 Exporter External Module includes a User Guide, Technical Guide and EM Changelog. These are all available through the _YES3 Exporter Documentation_ link.

REDCap project designers should consult the User Guide for detailed instructions and information about all the YES3 Exporter features.

Programmers, statisticians and system administrators should consult the Technical Guide for information about the exported data and data dictionaries.

The Change Log is a running report of feature updates and bug fixes.

## Introduction

The YES3 Exporter external module compliments REDCap's reports and data export tool by adding functionality to support data visualization, data sharing, and integration with statistical software and datamarts.

## Features

-   **Flexible output spreadsheet layouts** 
     -   Horizontal, vertical, and repeating form layouts
-   **Expeditiously handles large exports**
     -   Large volume exports have minimal impact on performance speed
     -   In testing it took 186 seconds to export 3,272 rows and 3,079 columns to a 33MB export csv file
-   **Enhanced data dictionary that includes metadata and data distributions** 
     -   Each export is accompanied by a customized data dictionary to simplify data sharing
-   **Detailed audit for every export download**
     -   Includes the ability to email daily activity summaries- automatically
-   **Incorporates form-specific user access and export rights**
     -   Compatible with REDCap v12 user-rights and backward compatible with earlier permissions models
-   **Supports deidentified and coded datasets** that mirror the REDCap parent system
-   **Any number of export templates may be designed and re-used**
-   **Export specifications can be rolled back and restored to a prior version**
-   **Can export directly to a host filesystem**
-   **Light and dark themes**

## About YES3

Our vision for the Yale Study Support Suite (YES3) is to provide an ‘off-the-shelf’ suite of external modules within REDCap that features popular, high-utility software tools to meet a wide variety of clinical research needs.

## Configuration

### System Configuration

The YES3 Exporter performs daily tasks, such as removing old export backups and emailing activity reports. By default, daily housekeeping tasks are run at 11 minutes past midnight, but an administrator can specify a different time to run these tasks.

To configure, go to Control Panel -> External Modules -> Manage -> Yes3 Exporter ->Configure

The time and results of the most recent daily housekeeping run for all projects are displayed in the YES3 Exporter system configuration page.

### Project Configuration

Only users with project design and setup rights can configure the External Module Settings for a project.

Project settings include rules for retaining export specification backups, whether and where to send daily log reports, and settings for host filesystem exports.

The date and result of the latest housekeeping run for the project are displayed in the Project Settings.

To configure, go to External Modules -\> Manage -\> Yes3 Exporter -\>Configure

#### Daily Activity Log Report

When enabled, you can designate one email address that will receive a daily summary of project export activity including: (1.) date and time of report, (2.) REDCap project information, and (3.) details about download and export activity in the last 24 hours.

To disable the daily log report, you may change the email notification setting to “no” at any time.

#### Host File System Exports

YES3 Exporter "payloads" can be written to a designated host file system folder (i.e. automounting to secure institutional file shares) which, among other things, is intended to support Datamart integration. To safeguard data security, enabling this setting requires approval by a REDCap administrator. 

> A payload consists of exported data, a comprehensive data dictionary and an information file having details about the export and project. See the Technical Guide for more information on YES3 Exporter payloads and datamart considerations.

#### Backup Retention

Export specifications can be rolled back and restored to a prior saved version. By default, 20 generations are retained, but you may choose to retain more or all generations.

## Feedback

Please send any bug reports or feature suggestions to the contact email address below. We welcome any comments, criticisms or suggestions.

## Collaboration

A link to the public GitHub repository is provided on the REDCap Consortium's External Module Repository entry for this EM. We welcome any feedback about our design and code that you may have, and will consider any pull requests. 

## Contact

redcap@yale.edu 

## Authors

### EM Developer

-   Peter Charpentier, Yale University (retired) and CRI Web Tools LLC

### Contributors

**REDCap@Yale Team:**
-   Katy Araujo, Yale University
-   Venugopal Bhatia, Yale University
-   Brian Funaro, Yale University
-   Mary Geda, Yale University
-   Janet Miceli, Yale University
-   Sui Tsang, Yale University

## Funding

-   To support our work and ensure future opportunities for development, please acknowledge the software and funding.
-   The **YES3 Exporter** was funded by Yale’s Claude D. Pepper Older Americans Independence Center (OAIC) grant through a Development Project Award for the Operations Core, **3P30AG021342**.

## Copyright

Copyright © 2022 by Yale University
