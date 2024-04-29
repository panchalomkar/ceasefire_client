// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

define(['jquery', 'core/ajax', 'core/templates'], function($, Ajax, Templates) {

    return /** @alias module:tool_lp/form-user-selector */ {

        processResults: function(selector, results) {
            var users = [];
            $.each(results, function(index, user) {
                users.push({
                    value: user.userid,
                    label: user.fullname
                });
            });
            return users;
        },

        transport: function(selector, query, success, failure) {
            var promise;
            var capability = $(selector).data('capability');
            if (typeof capability === "undefined") {
                capability = '';
            }
            
            var departmentid = 0;

            var getUrlParameter = function getUrlParameter(sParam) {
                var sPageURL = window.location.search.substring(1),
                    sURLVariables = sPageURL.split('&'),
                    sParameterName,
                    i;

                for (i = 0; i < sURLVariables.length; i++) {
                    sParameterName = sURLVariables[i].split('=');

                    if (sParameterName[0] === sParam) {
                        return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
                    }
                }
            };

            
            if(!departmentid){
                var departmentid = getUrlParameter('departmentid');
            }
            
            if (typeof departmentid === "undefined") {
                departmentid = 0;
            }

            promise = Ajax.call([{
                methodname: 'local_team_search_users',
                args: {
                    query: query,
                    capability: capability,
                    departmentid: departmentid
                }
            }]);

            promise[0].then(function(results) {
                var promises = [],
                    i = 0;
                // Render the label.
                $.each(results.users, function(index, user) {
                    var ctx = user,
                        identity = [];
                    $.each(['userid', 'fullname'], function(i, k) {
                        if (typeof user[k] !== 'undefined' && user[k] !== '') {
                            ctx.hasidentity = true;
                            identity.push(user[k]);
                        }
                    });
                    ctx.identity = identity.join(', ');
                    promises.push(Templates.render('local_team/form-user-selector-suggestion', ctx));
                });

                // Apply the label to the results.
                return $.when.apply($.when, promises).then(function() {
                    var args = arguments;
                    $.each(results.users, function(index, user) {
                        user._label = args[i];
                        i++;
                    });
                    success(results.users);
                });

            }, failure);
        }

    };

});
