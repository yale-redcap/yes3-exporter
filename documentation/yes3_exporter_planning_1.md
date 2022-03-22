# PLANNING DOCUMENT 1/2  
MARCH 17, 2022

The purpose of this document is to help me plan the reorganization
of the exporter metadata.

### PROBLEM  

The evolution from the NIACROMS field mapper to the YES3 Exporter resulted in the architecture taking on "bolted on"
aspects that have become barriers to understanding the codebase, and which will make maintenance future extensions
extremely difficult. These are best fixed now, before deployment. Specific issues are enumerated within each section below.

### GOALS OF THIS DESIGN MODIFICATION

Three goals:  
1. formalize an export specification as follows:
    - settings: settings currently handled by the yes3_exporter_setup plugin
        - includes the "uSpec" or optional "upload specification" that is entered as a JSON string
    - items: the uSpec elements and REDCap forms and fields that drive the data dictionary
        - includes uSpec to REDCap field and value mappings
2. create a single plugin to manage settings and items
3. save an export specification into a single EM log entry

<div style="page-break-after: always;"></div>

## EVENT SETTINGS

```
yes3_fieldmapper_setup.js: FMAPR.saveExportSettings

services.php: saveExportSettings
              -> saveEventSettings
```
### EM Log parameters for Events
```
message: "export_events",  
setting: "export-events",  
export_events_json: encoding of 
[  
    {  
        event_id: REDCap event_id,  
        event_name: REDCap unique name,  
        event_prefix: default or edited prefix  
    },  
    ...  
]
```  
## EXPORT SETTINGS
```
yes3_fieldmapper_setup.js: FMAPR.saveExportSettings

services.php: saveExportSettings
              -> saveExportSpecification
```

<div style="page-break-after: always;"></div>

### EM Log parameters for Export Settings

```
message: "export_specification",  
user: REDCap username,
export_uuid: unique id,
removed: "1" or "0",
setting: "export-specification",  
export_specification_json: encoding of  
    {  
        export_uuid: unique id,
        export_name: name,
        export_layout: "h", "v" or "r",
        export_selection: "1"=all records, "2"=selected,
        export_criterion_field: REDCap field_name,
        export_criterion_event: REDCap event_id,
        export_criterion_value: see below,
        export_target: "download" or "filesystem",
        export_target_folder: REDCap host filesystem folder,
        export_max_label_length: integer,
        export_max_text_length: integer,
        export_inoffensive_text: "1" or null,
        removed: "1" or "0",
        mapping_specification: {
            name: uSpec name,
            description: uSpec brief description,
            version: (string) use semantic versioning,
            version_date: (string),
            elements: [
                {
                    name: uSpec element name,
                    type: see below,
                    label: label suitable for reports,
                    valueset: [
                        {
                            value: (string),
                            label: short label assoc w/value
                        }
                        ...
                    ]              
                }
                ...
            ]
        }
    }
```

<div style="page-break-after: always;"></div>

#### NOTES  

*valid upload element types*:

"integer", "float", "nominal", "text", "date", "time", "datetime"

*export_criterion_value examples*:
```
"1"
"1, 2, 3"
"= 1"
"> 1"
"< 1"
">= 1"
"<= 1"
```  

#### ISSUES  
- conflation between "settings" and "specification" throughout
- "mapping specification" better referred to as "upload specification" or "uspec"

<div style="page-break-after: always;"></div>

## SPECIFICATION SETTINGS
```
yes3_fieldmapper.js: FMAPR.saveFieldMappings

services.php: saveFieldMappings
              -> saveExportSpecification
```
### EM Log parameters for Export Specifications
```
message: "YES3 Exporter Mappings",  
user: REDCap username,
setting: "yes3-exporter-field-map",  
field_mappings: json encoding of 
    [  
        {  
            yes3_fmapr_data_element_name: (uSpec element name or "redcap_element_xx"),
            yes3_fmapr_data_element_description: (uSpec ),
            element_origin: "redcap" or "specification",
            redcap_object_type: (if element_origin="redcap") "form" or "field",
            redcap_field_name: (if redcap_object_type="field"),
            redcap_form_name: (if redcap_object_type="form") REDcap form name or "all",
            redcap_event_id: REDCap event_id or "all",
            spec_type: (if element_origin="specification") uSpec data type,
            values: (uSpec valueset with mapped REDCap values) [
                {
                    yes3_fmapr_lov_value: ('value' from uSPec valueSet),
                    yes3_fmapr_lov_label: ('label' from uSpec valuset),
                    redcap_field_value: REDCap value mapped tp uSpec value
                }
            ]


        },  
        ...  
    ]
```

<div style="page-break-after: always;"></div>

#### NOTES
- "redcap_field_name" and "redcap_form_name" are mutually exclusive
- the "redcap_*" properties are populated only if element_origin="redcap"
- the "spec_type" and "values" properties are populated only if element_origin="specification"

#### ISSUES  
- "redcap_form_name" can be "all" which will cause an issue if there is a form with that name in the project
- "YES3 Exporter Mappings" EM log message better as "yes3_export_specification"
- "yes3-exporter-field-map" setting better as "yes3-export-specification"
- "yes3_fmapr_data_element_*" better as "element_*"
- "spec_type" better as "uspec_type"
- "values" better as "uspec_value_map"
- the origin "specification" is better as "uspec"


