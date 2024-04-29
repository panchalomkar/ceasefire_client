<?php
$records = $DB->get_records('custom_level');
$j = 1;
foreach ($records as $rec) {
    $array['id'][$j] = $rec->id;
    $array['level'][$j] = $rec->level;
    $array['point'][$j] = $rec->point;
    $array['grade'][$j] = $rec->grade;
    $array['icon'][$j] = $rec->icon;
    $j++;
}

?>
<form action="" method="POST" id="level_form1" enctype="multipart/form-data">
    <div id="slotform">
        <div class="m-4">
            <strong>
                <div class="row g-4">
                    <div class="col-1">
                        Level
                    </div>
                    <div class="col-3">
                        Rank
                    </div>
                    <div class="col-2">
                        Points
                    </div>
                    <div class="col-2">
                        Course Grade %
                    </div>
                    <div class="col-3">
                        Icon
                    </div>
                    <!--                    <div class="col-1">
                                            Action
                                        </div>-->
                </div>
            </strong>
        </div>
        <?php for ($i = 1; $i <= 10; $i++) { ?>
            <div class="m-4 formrow" id="1">
                <div class="row g-4">
                    <input type="hidden" name="id[]" value="<?php echo $array['id'][$i]; ?>">
                    <div class="col-1">
                        <?php echo $i; ?>
                    </div>
                    <div class="col-3">
                        <input type="text" name="level[]" class="form-control" placeholder="Level Name" value="<?php echo $array['level'][$i]; ?>">
                    </div>

                    <div class="col-2">
                        <input type="number" name="point[]" id="st1" tagid="1" class="form-control" placeholder="Point" value="<?php echo $array['point'][$i]; ?>">
                    </div>
                    <div class="col-2">
                        <input type="number" name="grade[]" id="et1" tagid="1" class="form-control" placeholder="Grade" value="<?php echo $array['grade'][$i]; ?>">
                    </div>
                    <div class="col-3">
                        <input type="file" min="1" name="icon[]" class="form-control"  style="width:200px;">
                        <img src="<?php echo $CFG->wwwroot.'/local/mydashboard/images/'.$array['icon'][$i]; ?>" width="50">
                    </div>
                    <!--                    <div class="col-2">
                                            <button class="btn btn-primary" id="addrow">Add Row</button>
                                        </div>-->
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="actionbutton">
        <input type="submit" name="submitbutton" id="formsubmit" class="btn btn-primary" value="Save">
    </div>
</form>
