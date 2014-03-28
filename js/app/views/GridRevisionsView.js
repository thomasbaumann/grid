var GridRevisionsView = Backbone.View.extend({
	tagName: 'table',
	events:{
		'click .btn-preview':'onPreview',
		'click .btn-delete':'onDelete',
		'click .btn-revert':'onRevert'
	},
	initialize:function(){
		this.listenTo(this.collection, 'add',this.render);
	},
	render:function(){
		var revisions=this.collection.toJSON();
		GRID.log(revisions);
		_.each(revisions,function(elem){
			elem.isDraft=false;
			elem.isPublished=false;
			elem.isDeprecated=false;
			if(elem.state=="published")
			{
				elem.isPublished=true;
			}
			else if(elem.state=="draft")
			{
				elem.isDraft=true;
			}
			else if(elem.state=="deprecated")
			{
				elem.isDeprecated=true;
			}
			elem["readable_date"] = "--.--.----";
			if(typeof elem["date"] != "undefined" && elem["date"] != "" && elem["date"] != null){
				var date = new Date(parseInt(elem["date"])*1000);
				elem["readable_date"] = date.getDate()+"."+(date.getMonth()+1)+"."+date.getFullYear();
			}
			if(typeof elem["editor"] == "undefined" || elem["editor"] == "" || elem["editor"] == null){
				elem["editor"] = "unknown";
			}
		});
		this.$el.html(ich.tpl_revisions({revisions:revisions,lang_values:document.lang_values}));
		return this;
	},
	onPreview:function(e){
		var revision=jQuery(e.srcElement).parents("tr").data("revision");
		var location =  GRID.PREVIEW_PATTERN.replace("{REV}", revision);
		window.open(location,"_blank");
		this.$el.parents(".rev-wrapper").toggle();
	},
	onDelete:function(e){
		GRID.revert();
		this.$el.parents(".rev-wrapper").toggle();
	},
	onRevert:function(e){
		var revision=jQuery(e.srcElement).parents("tr").data("revision");
		GRID.setToRevision(revision);
		this.$el.parents(".rev-wrapper").toggle();	
	},
});