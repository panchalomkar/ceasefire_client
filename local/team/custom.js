function allowe_limit_users_enrol(){
	var selected_team = $( "#selected_team option:selected" ).text();
	var regex = /\((.*?)\)/g
	var matches = [];
	var match = regex.exec(selected_team);
	while (match != null) {
	    matches.push(match[1]);
	    match = regex.exec(selected_team);
	}
	var total_users = eval(matches.join("+"));
	
	if(total_users > 3000){
		alert("Total Enrollments exceeding allowed capacity, please remove teams from selection to meet allowed capacity");
	}
}
