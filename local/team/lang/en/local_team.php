<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$string['pluginname'] = 'Team Managment';

$string['addteam'] = 'Add new team';
$string['allteams'] = 'All teams';
$string['anyteam'] = 'Any';
$string['assign'] = 'Assign';
$string['teammanagement'] = 'Team Management';
$string['assignto'] = 'Team \'{$a}\' members';
$string['backtoteams'] = 'Back to teams';
$string['bulkadd'] = 'Add to team';
$string['bulknoteam'] = 'No available teams found';
$string['categorynotfound'] = 'Category <b>{$a}</b> not found or you don\'t have permission to create a team there. The default context will be used.';
$string['team'] = 'Create Cohort';
$string['owner'] = "Captain";
$string['teams'] = 'Teams';
$string['teamsin'] = 'teams';
$string['assignteams'] = 'Assign team members';
$string['component'] = 'Source';
$string['contextnotfound'] = 'Context <b>{$a}</b> not found or you don\'t have permission to create a team there. The default context will be used.';
$string['csvcontainserrors'] = 'Errors were found in CSV data. See details below.';
$string['csvcontainswarnings'] = 'Warnings were found in CSV data. See details below.';
$string['csvextracolumns'] = 'Column(s) <b>{$a}</b> will be ignored.';
$string['currentusers'] = 'Current members';
$string['currentusersmatching'] = 'Current memebers matching';
$string['defaultcontext'] = 'Default context';
$string['delteam'] = 'Delete team';
$string['delconfirm'] = 'Do you really want to delete team \'{$a}\'?';
$string['description'] = 'Description';
$string['displayedrows'] = '{$a->displayed} rows displayed out of {$a->total}.';
$string['duplicateidnumber'] = 'Team with the same ID number already exists';
$string['editteam'] = 'Edit team';
$string['editteamidnumber'] = 'Edit team ID';
$string['editteamname'] = 'Edit team name';
$string['eventteamcreated'] = 'Team created';
$string['eventteamdeleted'] = 'Team deleted';
$string['eventteammemberadded'] = 'User added to a team';
$string['eventteammemberremoved'] = 'User removed from a team';
$string['eventteamupdated'] = 'Team updated';
$string['external'] = 'External team';
$string['idnumber'] = 'Team ID';
$string['memberscount'] = 'Team size(A)';
$string['name'] = 'Name';
$string['namecolumnmissing'] = 'There is something wrong with the format of the CSV file. Please check that it includes column names.';
$string['namefieldempty'] = 'Field name can not be empty';
$string['newnamefor'] = 'New name for team {$a}';
$string['newidnumberfor'] = 'New ID number for team {$a}';
$string['nocomponent'] = 'Created manually';
$string['potusers'] = 'Potential users';
$string['potusersmatching'] = 'Potential matching users';
$string['preview'] = 'Preview';
$string['removeuserwarning'] = 'Removing users from a team may result in unenrolling of users from multiple courses which includes deleting of user settings, grades, group membership and other user information from affected courses.';
$string['selectfromteam'] = 'Select members from team';
$string['systemteams'] = 'My teams';
$string['unknownteam'] = 'Unknown team ({$a})!';
$string['uploadteams'] = 'Upload teams';
$string['uploadedteams'] = 'Uploaded {$a} teams';
$string['useradded'] = 'User added to team "{$a}"';
$string['search'] = 'Search';
$string['searchteam'] = 'Search team';
$string['uploadteams_help'] = 'Teams may be uploaded via text file. The format of the file should be as follows:

* Each line of the file contains one record
* Each record is a series of data separated by commas (or other delimiters)
* The first record contains a list of fieldnames defining the format of the rest of the file
* Required fieldname is name
* Optional fieldnames are idnumber, description, descriptionformat, visible, context, category, category_id, category_idnumber, category_path
';
$string['visible'] = 'Visible';
$string['visible_help'] = "Any team can be viewed by users who have 'moodle/team:view' capability in the team context.<br/>
Visible teams can also be viewed by users in the underlying courses.";
$string['assignerror'] = 'You are not allowed to do this operation';
$string['teammanageerror'] = 'You are not allowed to manage this team';
$string['team:manageallteam'] = 'Manage all teams';
$string['team:view'] = 'View team';
$string['team:assign'] = 'Assgin member to team';
$string['team:manage'] = 'Manage own teams';
$string['manageteam'] = 'Manage team';
$string['unerollownererror'] = '<b>{$a}</b> is a captain, it can not be removed from the team, if you really want to remove captain then you need to change the ownership of this team';
$string['messageprovider:team_notification'] = 'Team notifications';
$string['viewmembers'] = 'View Members';
$string['pending'] = 'Pending';
$string['confirm'] = 'Confirm';
$string['reject'] = 'Reject';
$string['teammembers'] = 'Team Members';
$string['selectmentor']= 'Select mentor';
$string['choose']= 'Choose';
$string['selectcaptain'] = 'Select captain';
$string['mentorcaptainerror']= 'Mentor and captain can not be same';
// Team notifications
$string['notifyaddmembersubject'] = 'Invitation to join {$a}';
$string['notifyaddmembermessage'] = 'Hi {$a->name},'
        . '<p>You are invited to join "{$a->team}", if you want to be part of team then you need to click below link.</p>'
        . '<p><a href="{$a->clink}" target="_blank">click here</a></p>'
        . '<p>To reject this invitation <a href="{$a->rejectlink}" target="_blank">click here</a></p>'
        . '<p>Regards,'
        . '<br>{$a->captain}'
        . '<br>{$a->location}</p>';
$string['notifyremovemembersubject'] = 'Removed from {$a}';
$string['notifyremovemembermessage'] = 'Hi {$a->name},'
        . '<p>You are removed from {$a->team}.</p>';
$string['notifycaptainaddmembersubject'] = 'New member added to team "{$a}"';
$string['notifycaptainaddmember'] = 'Hi {$a->captain},'
        . '<p>{$a->member} is added to your team "{$a->team}"</p>';
$string['notifycaptainremovemembersubject'] = 'Member rejected your team "{$a}"';
$string['notifycaptainremovemember'] = 'Hi {$a->captain},'
        . '<p>{$a->member} is reject invitation of your team "{$a->team}"</p>';
$string['confirmusersuccess'] = 'you are added to team <b>\'{$a}\'</b> successfully';
$string['rejectusersuccess'] = 'you rejected to team <b>\'{$a}\'</b> successfully';
$string['confirmuserwarning'] = 'Failed to add you in the team <b>\'{$a}\'</b>';
$string['rejecteduserwarning'] = 'Failed to reject you from the team <b>\'{$a}\'</b>';
$string['notteammember'] = 'You are not a member of <b>\'{$a}\'</b> team';$string['assigndisable'] = "Can't add/remove member in team, team is now part of challenge";
$string['permissionerror'] = "You don't have permission";
$string['invalidcohort'] = "Team does not exist";
$string['invalidaccess'] = "Invalid department against team";
$string['noteamindepartment'] = 'No team found in selected department';
$string['teamddeddtocourse'] = 'Team "{$a->name}"added to course successfully';
$string['teamalreadyaddedtocourse'] = 'Team "{$a->name}" is already added to course';
$string['enrol'] = 'Enrol team';
$string['selectedteam'] = 'Select team';
$string['assignteam'] = 'Assign team';
$string['showteamcourses'] = 'Show All';
$string['selectcourse'] = 'Select Course';
$string['unenrollall'] = 'Unenroll All Users';
$string['student'] = 'Student';
$string['mentor'] = 'Mentor';
$string['removementorwarning'] = 'Removing mentor from the team is not recommended, if you did that may affect mentor role in every course where this team has been enrolled.';
$string['unenrolteam'] = 'Unassign team';
$string['enrol_exceed_error'] = 'Total Enrollments exceeding allowed capacity, please remove teams from selection to meet allowed capacity';
$string['enrolcapacity'] = 'Enrollments capacity';
$string['enrolment_allowed_text'] = 'Total Enrollments allowed for team management enrollment process.';
$string['enrol_required_team'] = 'Enrollments Capacity';
$string['download'] = 'Download';
$string['urlredirect'] = 'This page should automatically redirect. If nothing is happening please use the continue link below.';
$string['suspendedmemberscount'] = "Team size(S)";