<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$string['pluginname'] = 'Team Managment';

$string['addteam'] = 'إضافة فريق جديد';
$string['allteams'] = 'جميع الفرق';
$string['anyteam'] = 'أيما';
$string['assign'] = 'تعيين';
$string['teammanagement'] = 'إدارة الفريق';
$string['assignto'] = 'الفريق \'{$a}\' أعضاء';
$string['backtoteams'] = 'العودة إلي الفرق';
$string['bulkadd'] = 'إضافة إلى الفريق';
$string['bulknoteam'] = 'لا يوجد أي فرق متوافرة';
$string['categorynotfound'] = 'لم يتم العثور على الفئة المحددة <b> {$ a} </b> أو ليس لديك الإذن بإنشاء فريق هناك. سيتم استخدام السياق الافتراضي.';
$string['team'] = 'إنشاء مجموعة';
$string['owner'] = "كابتن";
$string['teams'] = 'الفرق';
$string['teamsin'] = 'الفرق';
$string['assignteams'] = 'تعيين أعضاء الفريق';
$string['component'] = 'المصدر';
$string['contextnotfound'] = 'لم يتم العثور على السياق المحدد <b> {$ a} </b> أو ليس لديك الإذن بإنشاء فريق هناك. سيتم استخدام السياق الافتراضي.';
$string['csvcontainserrors'] = 'تم العثور على أخطاء في بيانات CSV. انظر الى التفاصيل بالأسفل.';
$string['csvcontainswarnings'] = 'تم العثور على تحذيرات في بيانات CSV. انظر الى التفاصيل بالأسفل.';
$string['csvextracolumns'] = 'العامود(s) <b>{$a}</b> سيتم تجاهل.';
$string['currentusers'] = 'الأعضاء الحاليين';
$string['currentusersmatching'] = 'تطابق الأعضاء الحاليين';
$string['defaultcontext'] = 'السياق الافتراضي';
$string['delteam'] = 'حذف الفريق';
$string['delconfirm'] = 'هل ترغب حقًا بحذف الفريق \'{$a}\'؟';
$string['description'] = 'الوصف';
$string['displayedrows'] = '{$a->displayed} الصفوف المعروضة من {$a->المجموع}.';
$string['duplicateidnumber'] = 'يوجد بالفعل فريق بنفس رقم المعرف';
$string['editteam'] = 'تعديل الفريق';
$string['editteamidnumber'] = 'تعديل معرف الفريق';
$string['editteamname'] = 'تعديل اسم الفريق';
$string['eventteamcreated'] = 'تم إنشاء الفريق';
$string['eventteamdeleted'] = 'تم حذف الفريق';
$string['eventteammemberadded'] = 'تم إضافة المستخدم إلى فريق';
$string['eventteammemberremoved'] = 'تم حذف المستخدم من الفريق';
$string['eventteamupdated'] = 'تم تحديث الفريق';
$string['external'] = 'فريق خارجي';
$string['idnumber'] = 'معرف الفريق';
$string['memberscount'] = 'حجم الفريق (أ)';
$string['name'] = 'الاسم';
$string['namecolumnmissing'] = 'هناك خطأ ما في شكل ملف CSV. يُرجى التحقق من أنه يتضمن أسماء الأعمدة.';
$string['namefieldempty'] = 'لا يمكن ترك اسم الحقل فارغًا';
$string['newnamefor'] = 'اسم جديد للفريق {$a}';
$string['newidnumberfor'] = 'رقم تعريفي جديد للفريق {$a}';
$string['nocomponent'] = 'تم الإنشاء يدويًا';
$string['potusers'] = 'مستخدمين محتملين';
$string['potusersmatching'] = 'مستخدمون متطابقون محتملون';
$string['preview'] = 'Preview';
$string['removeuserwarning'] = 'قد تؤدي إزالة المستخدمين من فريق إلى إلغاء تسجيل المستخدمين من الدورات التدريبية المتعددة والتي تتضمن حذف إعدادات المستخدم والدرجات وعضوية المجموعة ومعلومات المستخدم الأخرى من الدورات التدريبية ذات الصلة.';
$string['selectfromteam'] = 'اختار أعضاء من الفريق';
$string['systemteams'] = 'فرقي';
$string['unknownteam'] = 'فريق غير معروف ({$a})!';
$string['uploadteams'] = 'تحميل الفرق';
$string['uploadedteams'] = 'المحملة {$a} الفرق';
$string['useradded'] = 'العضو المضاف إلى الفريق "{$a}"';
$string['search'] = 'البحث';
$string['searchteam'] = 'البحث عن الفرق';
$string['uploadteams_help'] = 'يمكن تحميل الفرق عبر ملف نصي. يجب أن يكون تنسيق الملف كما يلي:

* Each line of the file contains one record
* Each record is a series of data separated by commas (or other delimiters)
* The first record contains a list of fieldnames defining the format of the rest of the file
* Required fieldname is name
* Optional fieldnames are idnumber, description, descriptionformat, visible, context, category, category_id, category_idnumber, category_path
';
$string['visible'] = 'مرئي';
$string['visible_help'] = "يمكن مشاهدة أي فريق من قبل المستخدمين الذين لديهم إمكانية عرض  'moodle/team:view'في سياق الفريق.<br/>
يمكن أيضًا عرض الفرق المرئية بواسطة المستخدمين في الدورات التدريبية الأساسية.";
$string['assignerror'] = 'لا يسمح لك بالقيام بهذه العملية';
$string['teammanageerror'] = 'لا يسمح لك بإدارة هذا الفريق';
$string['team:manageallteam'] = 'إدارة جميع الفرق';
$string['team:view'] = 'عرض الفريق';
$string['team:assign'] = 'تعيين عضو في الفريق';
$string['team:manage'] = 'إدارة الفرق الخاصة بك';
$string['manageteam'] = 'إدارة الفريق';
$string['unerollownererror'] = '<b>{$a}</b> هو كابتن، لا يمكن إزالته من الفريق. إذا كنت تريد حقًا إزالة الكابتن، فأنت بحاجة إلى تغيير ملكية هذا الفريق';
$string['messageprovider:team_notification'] = 'إشعارات الفريق';
$string['viewmembers'] = 'عرض الأعضاء';
$string['pending'] = 'قيد الانتظار';
$string['confirm'] = 'تأكيد';
$string['reject'] = 'رفض';
$string['teammembers'] = 'أعضاء الفريق';
$string['selectmentor']= 'اختر المعلم الخاص';
$string['choose']= 'اختر';
$string['selectcaptain'] = 'اختر كابتن';
$string['mentorcaptainerror']= 'لا يمكن أن يكون نفس الشخص كابتن ومعلم خاص معًا';
// Team notifications
$string['notifyaddmembersubject'] = 'دعوى للانضمام {$a}';
$string['notifyaddmembermessage'] = 'مرحبًا {$a->name},'
        . '<p>أنت مدعو للانضمام إلى "{$ a-> team}"، إذا كنت تريد أن تكون جزءًا من الفريق، فعليك النقر على الرابط أدناه.</p>'
        . '<p><a href="{$a->clink}" target="_blank">انقر</a></p>'
        . '<p>لرفض هذه الدعوة <a href="{$a->rejectlink}" target="_blank">انقر هنا</a></p>'
        . '<p>تحياتي العطرة,'
        . '<br>{$a->captain}'
        . '<br>{$a->location}</p>';
$string['notifyremovemembersubject'] = 'محذوف من {$a}';
$string['notifyremovemembermessage'] = 'مرحبًا {$a->name},'
        . '<p>You are removed from {$a->team}.</p>';
$string['notifycaptainaddmembersubject'] = 'تم إضافة عضو جديد إلى الفريق "{$a}"';
$string['notifycaptainaddmember'] = 'مرحبًا {$a->captain},'
        . '<p>{$a->member} تم إضافته إلى فرقك "{$a->team}"</p>';
$string['notifycaptainremovemembersubject'] = 'رفض العضو فريقك "{$a}"';
$string['notifycaptainremovemember'] = 'مرحبًا {$a->captain},'
        . '<p>{$a->member} رفض دعوة فريقك "{$a->team}"</p>';
$string['confirmusersuccess'] = 'تم إضافتك إلى الفريق <b>\'{$a}\'</b> بنجاح';
$string['rejectusersuccess'] = 'لقد تم رفضك للفريق <b>\'{$a}\'</b> بنجاح';
$string['confirmuserwarning'] = 'فشل في إضافتك للفريق <b>\'{$a}\'</b>';
$string['rejecteduserwarning'] = 'فشل في رفضك من الفريق <b>\'{$a}\'</b>';
$string['notteammember'] = 'أنت لست عضو في <b>\'{$a}\'</b> الفريق';$string['assigndisable'] = "لا يمكن إضافة / إزالة عضو في الفريق، أصبح الفريق الآن جزءًا من الرفض";
$string['permissionerror'] = "ليس لديك الإذن";
$string['invalidcohort'] = "الفريق غير موجود";
$string['invalidaccess'] = "قسم غير صالح يخص الفريق";
$string['noteamindepartment'] = 'لم يتم العثور على فريق بالقسم المختار';
$string['teamddeddtocourse'] = 'تم إضافة "{$a->name}"الفريق إلى الدورة بنجاح';
$string['teamalreadyaddedtocourse'] = 'الفريق "{$a->name}" مضاف بالفعل إلى الدورة';
$string['enrol'] = 'تسجيل الفريق';
$string['selectedteam'] = 'اختيار الفريق';
$string['assignteam'] = 'تعيين الفريق';
$string['showteamcourses'] = 'إظهار الجميع';
$string['selectcourse'] = 'اختيار الدورة';
$string['unenrollall'] = 'إلغاء تسجيل جميع المستخدمين';
$string['student'] = 'الطالب';
$string['mentor'] = 'المعلم الخاص';
$string['removementorwarning'] = 'لا يوصى بإزالة المعلم الخاص من الفريق. في حال قمت بذلك، سيتأثر دوره في كل دورة تدريبية تم فيها تسجيل هذا الفريق.';
$string['unenrolteam'] = 'إلغاء تعيين الفريق';
$string['enrol_exceed_error'] = 'تجاوز إجمالي التسجيل السعة المسموح بها. يُرجى إزالة الفرق للوصول للسعة المسموح بها';
$string['enrolcapacity'] = 'السعة المسموح بها للاتحاق';
$string['enrolment_allowed_text'] = 'إجمالي عمليات التسجيل المسموح بها للتسجيل في إدارة الفريق.';
$string['enrol_required_team'] = 'السعة المسموح بها للاتحاق';
$string['download'] = 'تحميل';
$string['urlredirect'] = 'يجب أن توجهك هذه الصفحة تلقائيًا. في حال عدم حصول ذلك، يُرجى استخدام رابط المتابعة أدناه.';
$string['suspendedmemberscount'] = "حجم الفريق(S)";