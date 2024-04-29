YUI.add("moodle-block_xp-rulepicker",function(t,e){var i="block_xp",d={INFO:"info",PREFIX:"block_xp-rulepicker",RULE:"rule"},o=".rule",l=function(){l.superclass.constructor.apply(this,arguments)};t.namespace("M.block_xp").RulePicker=t.extend(l,M.core.dialogue,{prepared:!1,initializer:function(){this.publish("picked")},close:function(){this.hide()},display:function(){this.prepared||this.prepare(),this.show()},picked:function(e){e.preventDefault(),this.close(),this.fire("picked",e.currentTarget.getData("id"))},prepare:function(){var e,i;i=t.Handlebars.compile('<div> {{#rules}} <div class="{{../CSS.RULE}}" data-id="{{id}}" tabindex="0" role="button"       aria-describedby="block_xp_rulepicker_rule_title_{{id}}">   <h3 id="block_xp_rulepicker_rule_title_{{id}}">{{ name }}</h3>   {{#info}}   <div class="{{../../CSS.INFO}}">     {{ . }}   </div>   {{/info}} </div> {{/rules}}</div>'),this.getStdModNode(t.WidgetStdMod.HEADER).prepend(t.Node.create("<h1>"+this.get("title")+"</h1>")),e=t.Node.create(i({CSS:d,rules:this.get("rules")})),this.setStdModContent(t.WidgetStdMod.BODY,e,t.WidgetStdMod.REPLACE),this.get("boundingBox").one(".moodle-dialogue-wrap").addClass("moodle-dialogue-content"),this.get("boundingBox").delegate("click",this.picked,o,this),this.get("boundingBox").delegate("key",this.picked,"32, 13",o,this),this.prepared=!0}},{NAME:e,CSS_PREFIX:d.PREFIX,ATTRS:{rules:{validator:t.Lang.isObject,value:null}}}),t.Base.modifyAttrs(t.namespace("M.block_xp.RulePicker"),{modal:{value:!0},render:{value:!0},title:{valueFn:function(){return M.util.get_string("pickaconditiontype",i)}},visible:{value:!1},width:{value:"500px"}}),t.namespace("M.block_xp.RulePicker").init=function(e){return new l(e)}},"@VERSION@",{requires:["base","node","handlebars","moodle-core-notification-dialogue"]});