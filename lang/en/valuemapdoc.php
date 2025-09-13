<?php
// English language strings for mod_valuemapdoc

$string['pluginname'] = 'Value Map Generator';
$string['modulename'] = 'Value Map';
$string['modulenameplural'] = 'Value Maps';

$string['addentry'] = 'Add new record';
$string['actions'] = 'Actions';

$string['includemaster'] = 'Show master entries';

$string['backtogenerator'] = 'Back to generator';
$string['gotocontent'] = 'Generated content';

$string['advancedsettings'] = 'Advanced settings';


$string['entrydeleted'] = 'Value Map entry deleted.';
// Markets/Customers management
$string['markets'] = 'Markets & Customers';
$string['addmarket'] = 'Add new market';
$string['market'] = 'Market';
$string['customer'] = 'Customer';
$string['opportunity'] = 'Opportunity';
$string['person'] = 'Person';

// Market types
$string['type_market'] = 'Market';
$string['type_customer'] = 'Customer';
$string['type_opportunity'] = 'Opportunity';
$string['type_person'] = 'Person';

// Form fields
$string['markettype'] = 'Type';
$string['marketname'] = 'Name';
$string['marketdescription'] = 'Description';
$string['parentmarket'] = 'Parent';

// Actions
$string['addmarket'] = 'Add Market/Customer';
$string['editmarket'] = 'Edit Market/Customer';
$string['deletemarket'] = 'Delete';
$string['actions'] = 'Actions';

// Messages
$string['marketcreated'] = 'Market/Customer created successfully';
$string['marketupdated'] = 'Market/Customer updated successfully';
$string['marketdeleted'] = 'Market/Customer deleted successfully';
$string['nomarkets'] = 'No markets/customers found';
$string['confirmdeletemarket'] = 'Are you sure you want to delete this entry?';

// Validation
$string['duplicatename'] = 'Name already exists for this type in this course';

// For basic help
$string['show_template_help'] = 'Show template help';
$string['show_basic_help_desc'] = 'Display instructions on how to use placeholder codes in the template';

// For markets help
$string['show_markets_help'] = 'Show field reference';
$string['show_markets_help_desc'] = 'Display a complete list of available fields that can be inserted';

// General
$string['none'] = 'None';
$string['choose'] = 'Choose...';

// Hierarchy
$string['hierarchy_explanation'] = 'Structure: Market → Customer → (Opportunity | Person)';
$string['step1_add_market'] = 'Step 1: Add a Market';
$string['invalid_hierarchy'] = 'Invalid parent-child relationship';
$string['parentselection'] = 'Parent Selection';
$string['parentselection_help'] = 'Select the appropriate parent based on the hierarchy rules.';


// Error messages
$string['duplicate_name'] = 'A record with this name already exists in this context';
$string['invalid_hierarchy'] = 'Invalid parent-child relationship';
$string['record_not_found'] = 'Record not found';

//Content generation
$string['contentdeleted'] = 'Content deleted.';
$string['selectedentries'] = 'Selected records';
$string['ismaster'] = 'Master Value Map';
$string['ismaster_label'] = 'All records added to Master Value Map will be visible to all users in this course.';

$string['previewdocument'] = 'Preview document';
$string['masterentrycopyinfo'] = 'This entry comes from a Master activity. Saving will create a local copy.';

$string['ratefeedback'] = 'How this content help you?';
$string['optionalcomment'] = 'Describe your experience with this content...';
$string['rategeneratedfile'] = 'Rate content';
$string['useful_yes'] = 'It worked WELL';
$string['useful_maybe'] = 'Not sure OR Not used';
$string['useful_no'] = 'Not helpful';

$string['exporttocsv'] = 'Export to CSV';
$string['importfromcsv'] = 'Import from CSV';
$string['imported'] = 'Entries imported successfully.';

$string['previewonly'] = 'Preview only';
$string['previewonly_help'] = 'If enabled, the document will be generated but not saved. This is useful for testing purposes.';
$string['previewonly_label'] = 'Preview only';
$string['previewonly_desc'] = 'If enabled, the document will be generated but not saved. This is useful for testing purposes.';


$string['targetactivity'] = 'Target activity for file storage';
$string['targetactivity_help'] = 'Select a course activity (such as Folder) where generated documents should be saved.';
$string['nofolderavailable'] = 'No supported file activities available in this course.';

$string['output_userfolder'] = 'User Private Files';

$string['moddescription'] = 'Description';
$string['generalsettings'] = 'General settings';
$string['chatgptsettings'] = 'ChatGPT settings';
$string['activity_prompt'] = 'System prompt for ChatGPT';
$string['activity_prompt_help'] = 'This prompt will be added to the default system prompt when generating documents using AI.';

$string['market'] = 'Market';
$string['industry'] = 'Industry';
$string['role'] = 'Role';
$string['businessgoal'] = 'Business Goal';
$string['strategy'] = 'Strategy';
$string['difficulty'] = 'Difficulty';
$string['situation'] = 'Diff. situation';
$string['statusquo'] = 'Status Quo';
$string['coi'] = 'Cost of Inaction';
$string['differentiator'] = 'Product / Service';
$string['impact'] = 'POV: Reframe';
$string['newstate'] = 'New situation';
$string['successmetric'] = 'Success Factors';
$string['impactstrategy'] = 'Impact on Strategy';
$string['impactbusinessgoal'] = 'Impact on Business Goal';
$string['impactothers'] = 'Impact on Other People';
$string['proof'] = 'Proof of New situation';
$string['time2results'] = 'Time to Results';
$string['quote'] = 'Client Quote';
$string['clientname'] = 'Client Names';

$string['editentry'] = 'Edit Value Map Entry';
$string['noentries'] = 'No value map entries found.';
$string['entryupdated'] = 'Value Map entry updated.';

$string['generatedocument'] = 'Generate Document';
$string['selectentries'] = 'Select entries';
$string['opportunityname'] = 'Opportunity Name';
$string['templatetype'] = 'Template Type';

$string['documentsaved'] = 'Document has been saved successfully.';
$string['nosavefolder'] = 'Document could not be saved (no folder found).';

$string['customprompt'] = 'Custom ChatGPT prompt';
$string['customprompt_help'] = 'You can enter additional instructions for ChatGPT here, e.g., "prepare this as a LinkedIn post" or "make it persuasive for a CFO".';

$string['noentriesselected'] = 'You must select at least one Value Map entry to generate a document.';

$string['savedocument'] = 'Save document';
$string['savedocument_help'] = 'If enabled, the generated document will be saved to the selected location. If disabled, only a preview will be shown.'; 
$string['backtogeneration'] = 'Back to document generation';
$string['documentgenerated'] = 'Document has been generated successfully.'; 


$string['templateprompt'] = 'Custom ChatGPT prompt for this template';

$string['openai_apikey'] = 'OpenAI API Key';
$string['openai_apikey_desc'] = 'Enter your OpenAI API key to enable document generation.';
$string['openai_model'] = 'Model';
$string['openai_model_desc'] = 'Specify which OpenAI model to use (e.g., gpt-4).';
$string['default_system_prompt'] = 'Default System Prompt';
$string['default_system_prompt_desc'] = 'Prompt that defines how ChatGPT behaves globally. Used as the base for all documents.';

$string['default_template_prompt'] = 'Default Template Prompt';
$string['default_template_prompt_desc'] = 'Prompt that defines how ChatGPT behaves for this template. Used as the base for all documents generated with this template.';
$string['default_template_prompt_help'] = 'This prompt will be added to the default system prompt when generating documents using this template.';


$string['activity_prompt'] = 'Activity-specific Prompt';
$string['activity_prompt_help'] = 'You can specify an additional prompt that will be appended to the system prompt when generating documents.';

$string['templatesadmin'] = 'Document Templates';
$string['templatesaved'] = 'Template has been saved.';
$string['notemplates'] = 'No templates available.';

$string['eventrecord_added'] = 'Record added';
$string['eventrecord_viewed'] = 'Record viewed';
$string['eventdocument_generated'] = 'Document generated';
$string['eventdocument_saved'] = 'Document saved';
$string['eventdocument_rated'] = 'Document rated';

$string['filename'] = 'File name';
$string['template'] = 'Template';
$string['result'] = 'Effectiveness';
$string['action'] = 'Action';
$string['ratedocument'] = 'Rate document';
$string['nodocuments'] = 'No documents found for this opportunity.';
$string['ratingsaved'] = 'Rating has been saved.';

$string['templatename'] = 'Template name';
$string['templatetype'] = 'Template type';

$string['templatesaved'] = 'Template saved successfully.';
$string['saveasnew'] = 'Save as new';
$string['entrysavedasnew'] = 'The entry has been saved as a new record.';


// Dodaj te ciągi do pliku lang/en/valuemapdoc.php

$string['description'] = 'Description';
$string['description_help'] = 'Brief description of the template purpose and usage.';

$string['templatefields'] = 'Template fields';
$string['templatefields_help'] = 'Comma-separated list of field names that this template uses. Example: name, email, phone, address';

$string['templatecontent'] = 'Template content';
$string['invalidfieldname'] = 'Invalid field name: {$a}. Field names must start with a letter or underscore and contain only letters, numbers, and underscores.';

// Dodaj do pliku lang/en/valuemapdoc.php

$string['templatesadmin'] = 'Templates Administration';
$string['addtemplate'] = 'Add New Template';
$string['existingtemplates'] = 'Existing Templates';
$string['templatename'] = 'Template Name';
$string['templatetype'] = 'Template Type';
$string['usage'] = 'Usage Count';
$string['actions'] = 'Actions';
$string['cannotdeleteused'] = 'Cannot delete - template is in use';
$string['confirmdelete'] = 'Are you sure you want to delete this template?';
$string['notemplates'] = 'No Templates Found';
$string['notemplates_desc'] = 'There are no templates configured yet. Create your first template to get started.';
$string['nodescription'] = 'No description provided';
$string['inuse'] = 'In Use';
$string['templates'] = 'templates';
$string['uncategorized'] = 'Uncategorized';
$string['duplicate'] = 'Duplicate';
$string['confirmduplicate'] = 'Are you sure you want to duplicate this template?';
$string['copyof'] = 'Copy of {$a}';
$string['templateduplicatedsuccess'] = 'Template "{$a}" has been successfully duplicated.';
$string['templateduplicationfailed'] = 'Failed to duplicate template. Please try again.';
$string['templatedeletedsuccess'] = 'Template "{$a}" has been successfully deleted.';
$string['templatedeletionfailed'] = 'Failed to delete template. Please try again.';


//
$string['documentactions'] = 'Document Actions';
$string['documentsettings'] = 'Document Settings';
$string['visibility'] = 'Visibility';
$string['documentpreview'] = 'Document Preview';
$string['ratecontent'] = 'Rate This Content';
$string['editcontent'] = 'Edit this document';
$string['tunecontent'] = 'Fine-tune this document';
$string['deletecontent'] = 'Delete this document';
$string['confirmdelete'] = 'Are you sure you want to delete this document? This action cannot be undone.';

//markets
$string['basicinformation'] = 'Basic Information';
$string['additional_information'] = 'Additional Information';

$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['existingtemplates'] = 'Existing templates';
$string['cannotdeleteused'] = 'Used in generated documents – cannot delete.';

$string['edittemplate'] = 'Edit document template';
$string['addtemplate'] = 'Add new template';
$string['templatedeleted'] = 'Template deleted';
$string['pluginadministration'] = 'Value Map Document administration';

$string['available_placeholders'] = 'Available Placeholders';
$string['templatebody'] = 'Template body';
$string['templatebody_help'] = 'This is the content of the document template.
<br>
You can use data from the Value Map by inserting placeholders in square brackets: [fieldname].
<br>
The following placeholders are available:<br>
<ul>
<li> {{market}} – Target market (e.g. Software producers)
<li> {{industry}} – Industry / sector
<li> {{role}} – Key buyer role (e.g. CEO)
<li> {{businessgoal}} – Main business goal (e.g. Improve profitability)
<li> {{strategy}} – Strategy for achieving the goal
<li> {{difficulty}} – Obstacle to the strategy
<li> {{situation}} – Current observable situation
<li> {{statusquo}} – Typical action taken today
<li> {{coi}} – Cost of Inaction
<li> {{differentiator}} – Key differentiator of your solution
<li> {{impact}} – How the differentiator helps overcome the difficulty
<li> {{newstate}} – New situation after implementation
<li> {{successmetric}} – KPI / success measure
<li> {{impactstrategy}} – Impact on strategy
<li> {{impactbusinessgoal}} – Impact on business goal
<li> {{impactothers}} – Impact on other stakeholders
<li> {{proof}} – Proof / case study / example
<li> {{time2results}} – Estimated time to results
<li> {{quote}} – Customer quote
<li> {{clientname}} – Client name or reference
</ul>
<p>📌 Example:<br>
<br>
"Our client {{clientname}} in the {{market}} market wanted to {{businessgoal}} through {{strategy}}.<br>  
They struggled with {{difficulty}}, especially {{situation}}. <br> 
Our solution helps by {{differentiator}}, resulting in {{newstate}}.<br>  
KPIs improved by {{successmetric}}, delivering {{impactbusinessgoal}}."<br>
</p><br>
Each placeholder will be replaced with the corresponding data from the selected Value Map entry (or entries) during document generation.';


// Help strings for entry_form.php fields
$string['market_help'] = 'Target market for this situation.<br><ul><li>What market does the client operate in?</li><li>What are its characteristics?</li><li>Is it local, regional, or global?</li></ul>';
$string['industry_help'] = 'Client\'s industry or sector.<br><ul><li>What industry is it (e.g., manufacturing, education, IT)?</li><li>Are there common challenges in this industry?</li><li>What trends or regulations impact it?</li></ul>';
$string['role_help'] = 'Role of the person we talk to.<br><ul><li>Who is the person (organizational role)?</li><li>Do they influence decisions?</li><li>What are their goals and KPIs?</li></ul>';
$string['businessgoal_help'] = 'Key business goal of the client.<br><ul><li>What does the client want to achieve?</li><li>How is success measured?</li><li>Is there a strategic or time frame for the goal?</li></ul>';
$string['strategy_help'] = 'Strategy to achieve the business goal.<br><ul><li>What actions are planned?</li><li>What resources are involved?</li><li>Is it a new or ongoing strategy?</li></ul>';
$string['difficulty_help'] = 'Difficulty the client is facing.<br><ul><li>What hinders the goal?</li><li>Is it organizational, technical, or people-related?</li><li>How long has it been an issue?</li></ul>';
$string['situation_help'] = 'Client\'s current situation context.<br><ul><li>What is happening now?</li><li>Any projects underway?</li><li>Any recent external changes?</li></ul>';
$string['statusquo_help'] = 'Current approach the client uses.<br><ul><li>How is the problem currently handled?</li><li>Are any solutions in place?</li><li>What are the limits of that approach?</li></ul>';
$string['coi_help'] = 'Cost of Inaction (COI).<br><ul><li>What happens if nothing is done?</li><li>Financial or operational consequences?</li><li>Who suffers the most?</li></ul>';
$string['differentiator_help'] = 'What sets us apart?<br><ul><li>Why choose us?</li><li>What unique capabilities do we offer?</li><li>Do we have proof of effectiveness?</li></ul>';
$string['impact_help'] = 'Impact of our solution on the difficulty.<br><ul><li>Does it remove the problem?</li><li>To what extent?</li><li>Will the client notice the change?</li></ul>';
$string['newstate_help'] = 'New state after solution is implemented.<br><ul><li>How will the situation change?</li><li>What will the “post-change” world look like?</li><li>Can it be measured?</li></ul>';
$string['successmetric_help'] = 'Success metric – how the client will know the goal is met.<br><ul><li>What will be measured?</li><li>Which KPIs matter?</li><li>What data is already available?</li></ul>';
$string['impactstrategy_help'] = 'Impact on the client\'s strategy.<br><ul><li>Will it accelerate the strategy?</li><li>Make implementation easier?</li><li>Improve alignment of efforts?</li></ul>';
$string['impactbusinessgoal_help'] = 'Impact on the business goal.<br><ul><li>To what extent does it help achieve the goal?</li><li>Is the impact direct or indirect?</li><li>Can it be quantified?</li></ul>';
$string['impactothers_help'] = 'Impact on other people/stakeholders.<br><ul><li>Who else is affected?</li><li>Are there other stakeholders who benefit?</li><li>Are there concerns or objections?</li></ul>';
$string['proof_help'] = 'Proof – case study, data, quotes.<br><ul><li>Do we have a similar success story?</li><li>Can we show numbers?</li><li>Can the client verify it?</li></ul>';
$string['time2results_help'] = 'Time to results.<br><ul><li>When can the client expect outcomes?</li><li>What stages are involved?</li><li>Can we show early wins?</li></ul>';
$string['quote_help'] = 'Client quote – in their own words.<br><ul><li>What did the client say that shows the value of the issue or solution?</li><li>Is the quote strong and authentic?</li><li>Does it convey emotion or conviction?</li></ul>';
$string['clientname_help'] = 'Client name – company or person (optional).<br><ul><li>Who is the story about?</li><li>Can it be published?</li><li>Has the client given consent?</li></ul>';

$string['startover'] =  'Start over';
$string['startover_help'] = 'Start over with a new document generation. This will clear all current selections and settings.';
$string['startover_desc'] = 'Start over with a new document generation. This will clear all current selections and settings.';

$string['editcontent'] = 'Edit generated content';
$string['documentcontent'] = 'Generated document content';
$string['documentcontent_help'] = 'This is the generated text based on your Value Map entries and selected template. You can review and modify it before saving.';
$string['contentprepared'] = 'The content has been prepared. You can now finalize and save it.';


$string['tuning']   = 'Tuning your content';
$string['tuning_help']   = 'Tuning your content with ChatGPT. You can edit the generated text and provide a custom prompt to refine the output.';

$string['tunecontent'] = 'Tune Your Content';
$string['originaltext'] = 'Original Text';
$string['previoustext'] = 'Previous Text';
$string['tunedtext'] = 'Tuned Text';
$string['savechanges'] = 'Save changes';
$string['tune'] = 'Tune';

// Prompt field support
$string['promptlabel'] = 'How adjust text:';
$string['promptlabel_help'] = 'This field allows you to guide the tuning of generated content. Add extra instructions such as "make it more formal", "summarize the main points", or "highlight the benefits for a CFO".';
$string['promptplaceholder'] = 'Rewrite the text to sound more persuasive...';

$string['startagain'] = 'Start Again';
$string['addtoedit'] = 'Add to Edit';
$string['movetoedit'] = 'Move to Edit';


$string['viewcontent'] = 'View content';
$string['generateddocuments'] = 'Content';
$string['includeotherusersdocs'] = 'Include documents from other users';


$string['emailsubject'] = 'Your generated document from ValueMap';
$string['emailintro'] = 'You have successfully saved a document. Below is a copy of its content:';


// Toolbar labels
$string['tuningoptionstoolbar'] = 'Tuning options toolbar';
$string['tuninggroup'] = 'Tuning options group';

// Button labels
$string['formal'] = 'Formal';
$string['friendly'] = 'Friendly';
$string['short'] = 'Short';
$string['value'] = 'Value Focused';
$string['dynamic'] = 'Dynamic';

// Tooltips
$string['tooltip_formal'] = 'Rewrite the text to be more formal, polite, and professional.';
$string['tooltip_friendly'] = 'Rewrite the text to sound more friendly, natural, and personal.';
$string['tooltip_short'] = 'Shorten the text to make it more concise while keeping the main idea.';
$string['tooltip_value'] = 'Rewrite the text to better highlight the client\'s value and benefits.';
$string['tooltip_dynamic'] = 'Rewrite the text to be more energetic and encourage the reader to take action.';


//Rate content
$string['saveasfile'] = 'Save as file';
$string['sendbymail'] = 'Send by email';
$string['copytoclipboard'] = 'Copy to clipboard';
$string['settingssaved'] = 'Settings have been saved.';
$string['emailsent'] = 'Email sent successfully.';
$string['emailfailed'] = 'Failed to send email.';

$string['startover'] =  'Start over';
$string['startover_help'] = 'Start over with a new document generation. This will clear all current selections and settings.<ul><li>All selected Value Map entries will be cleared</li><li>Any unsaved changes will be lost</li><li>You will return to the initial step</li></ul>';
$string['startover_desc'] = 'Start over with a new document generation. This will clear all current selections and settings.';

$string['editcontent'] = 'Edit generated content';
$string['documentcontent'] = 'Generated document content';
$string['documentcontent_help'] = 'This is the generated text based on your Value Map entries and selected template. <ul><li>Review the content before saving</li><li>You can modify the text as needed</li><li>Changes will be saved in the final version</li></ul>';
$string['contentprepared'] = 'The content has been prepared. You can now finalize and save it.';

$string['tuning']   = 'Tuning your content';
$string['tuning_help']   = 'Tune your generated content with ChatGPT. <ul><li>Edit the generated text directly</li><li>Provide a custom prompt to refine the output</li><li>Use toolbar options for quick adjustments</li></ul>';
$string['tunecontent'] = 'Tune Your Content';
$string['originaltext'] = 'Original Text';
$string['previoustext'] = 'Previous Text';
$string['tunedtext'] = 'Tuned Text';
$string['savechanges'] = 'Save changes';
$string['tune'] = 'Tune';

// Prompt field support
$string['promptlabel'] = 'How to adjust text:';
$string['promptlabel_help'] = 'This field allows you to guide the tuning of generated content. <ul><li>Add extra instructions such as "make it more formal", "summarize the main points", or "highlight the benefits for a CFO".</li><li>Leave blank for default improvement.</li></ul>';
$string['promptplaceholder'] = 'Rewrite the text to sound more persuasive...';

$string['startagain'] = 'Start Again';
$string['addtoedit'] = 'Add to Edit';
$string['movetoedit'] = 'Move to Edit';

$string['viewcontent'] = 'View content';
$string['generateddocuments'] = 'Content';
$string['includeotherusersdocs'] = 'Include documents from other users';

$string['emailsubject'] = 'Your generated document from ValueMap';
$string['emailintro'] = 'You have successfully saved a document. Below is a copy of its content:';

// Toolbar labels
$string['tuningoptionstoolbar'] = 'Tuning options toolbar';
$string['tuninggroup'] = 'Tuning options group';

// Button labels
$string['formal'] = 'Formal';
$string['friendly'] = 'Friendly';
$string['short'] = 'Short';
$string['value'] = 'Value Focused';
$string['dynamic'] = 'Dynamic';

// Tooltips
$string['tooltip_formal'] = 'Rewrite the text to be more formal, polite, and professional.';
$string['tooltip_friendly'] = 'Rewrite the text to sound more friendly, natural, and personal.';
$string['tooltip_short'] = 'Shorten the text to make it more concise while keeping the main idea.';
$string['tooltip_value'] = 'Rewrite the text to better highlight the client\'s value and benefits.';
$string['tooltip_dynamic'] = 'Rewrite the text to be more energetic and encourage the reader to take action.';

// prompts
$string['formal_prompt'] = 'Rewrite the text to be more formal, polite, and professional.';
$string['friendly_prompt'] = 'Rewrite the text to sound more friendly, natural, and personal.';
$string['short_prompt'] = 'Shorten the text to make it more concise while keeping the main idea.';
$string['value_prompt'] = 'Rewrite the text to better highlight the client\'s value and benefits.';
$string['dynamic_prompt'] = 'Rewrite the text to be more energetic and encourage the reader to take action.';

// Export, email, copy/save
$string['saveasfile'] = 'Save as file';
$string['sendbymail'] = 'Send by email';
$string['copytoclipboard'] = 'Copy to clipboard';


$string['documentvisibility'] = 'Visibility';
$string['visibility_private'] = 'Only me';
$string['visibility_shared'] = 'ALL users';

$string['nogroupaccess'] = 'You cannot access this activity because you are not a member of any group.';
$string['nopermission'] = 'You do not have permission to access this resource.';