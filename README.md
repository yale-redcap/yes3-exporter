![codeql workflow](https://github.com/yale-redcap/yes3-exporter/actions/workflows/codeql-javascript.yml/badge.svg)
![sonarcloud workflow](https://github.com/yale-redcap/yes3-exporter/actions/workflows/sonarcloud.yml/badge.svg)


# YES3 Exporter README

REDCap External Module  
Version 1.0.4, February 2023

## Important note about documentation

The external module includes a User Guide, Technical Guide and EM Changelog. These are all available through the _YES3 Exporter Documentation_ link. You may also view them on GitHub by clicking the links provided below.
-   **User Guide** 
     -   Purpose: How-to tutorial and information 
     -   Audience: REDCap Project Designers  
     -   [Click here to view the YES3 Exporter User Guide on GitHub](https://github.com/yale-redcap/yes3-exporter/blob/main/documents/userguide.md)
-   **Technical Guide** 
     -   Purpose: Explains functionality, technical specifications about the exported data, the data dictionaries, and the software design.
     -   Audience: Analysts, Statisticians, Software Designers/Programmers  
     -   [Click here to view the YES3 Exporter Technical Guide on GitHub](https://github.com/yale-redcap/yes3-exporter/blob/main/documents/technical.md)
-   **Change Log** 
     -   Purpose: Basic information about feature updates and bug fixes
     -   Audience: REDCap System Administrators
     -   [Click here to view the YES3 Exporter Change Log on GitHub](https://github.com/yale-redcap/yes3-exporter/blob/main/documents/changelog.md)

## Introduction and Features

The YES3 Exporter external module complements REDCap's reports and data export tool by adding functionality to support integration with statistical software and datamarts, including host file system exports.  Major features include flexible layouts (horizontal, vertical, repeating form), fast performance speed, and export-specific data dictionaries.  Other helpful built-in features include detailed audit reports, metadata and data distributions within the data dictionary, and the ability to roll-back export templates. The user-right functionality, ability to design and re-use export templates, and the ability to generate deidentified/coded datasets mirrors that of the parent REDCap system.  UI/UX elements include light/dark theme, embedded help menus, floating instruction windows, filters, and more....

## Configuration

### System Configuration

The YES3 Exporter performs daily tasks, such as removing old export backups and emailing activity reports. By default, daily housekeeping tasks are run at 11 minutes past midnight, but an administrator can specify a different time to run these tasks.  The time and results of the most recent daily housekeeping run for all projects are displayed in the YES3 Exporter system configuration page.

To configure, go to Control Panel -> External Modules -> Manage -> Yes3 Exporter ->Configure

### Project Configuration

Only users with project design and setup rights can configure the External Module Settings for a project. Project settings include rules for retaining export specification backups, whether and where to send daily log reports, and settings for host filesystem exports. The date and result of the latest housekeeping run for the project are displayed in the Project Settings.

To configure, go to External Modules -\> Manage -\> Yes3 Exporter -\>Configure

#### Activity Log Report

When enabled, you can designate one email address that will receive a notification and summary of project export activities including: (1.) date and time of report, (2.) REDCap project information, and (3.) details about download and export activity in the last 24 hours. Daily log reports are only released when exports occur.   

To disable the daily log report, you may change the email notification setting to “no” at any time.

#### Host File System Exports

YES3 Exporter "payloads" can be written to a designated host file system folder (i.e. automounting to secure institutional file shares) which, among other things, is intended to support Datamart integration. To safeguard data security, enabling this setting requires approval by a REDCap administrator. 

> A payload consists of exported data, a comprehensive data dictionary and an information file having details about the export and project. See the Technical Guide for more information on YES3 Exporter payloads and datamart considerations.

#### Backup Retention

Export specifications can be rolled back and restored to a prior saved version. By default, 20 generations are retained, but you may choose to retain more or all generations.

## Feedback

Please send any bug reports or feature suggestions to the contact email address below. We welcome comments, criticisms, suggestions and, of course, positive feedback!

## Collaboration

A link to the public GitHub repository is provided on the REDCap Consortium's External Module Repository entry for this EM. We welcome any feedback about our design and code that you may have, and will consider any pull requests. 

## Contact

redcap@yale.edu 


## About YES3

Our vision for the Yale Study Support Suite (YES3) is to provide an ‘off-the-shelf’ suite of external modules within REDCap that features popular, high-utility software tools to meet a wide variety of epidemiological and clinical research needs.

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
