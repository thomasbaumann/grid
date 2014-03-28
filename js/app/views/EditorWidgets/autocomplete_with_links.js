boxEditorControls['autocomplete-with-links']=Backbone.View.extend({
    events:{
        "keyup .i-autocomplete":"keyup",
        "click .suggestion-list li":"listItemSelected",
        "click .cancel":"cancelSelection",
    },
    initialize:function(){

    },
    render:function(){
        var html="<label>"+this.model.structure.label+"</label>";
        var classes="autocomplete-wrapper form-autocomplete dynamic-value";
        var disabled="";
        var fetch=false;
        if(this.model.container[this.model.structure.key]!='' ||
            this.model.container[this.model.structure.key]===0)
        {
            classes+=" locked";            
            disabled="disabled=disabled";
            fetch=true;
        }
        html+="<div class='"+classes+"'><input type=text class='form-text autocomplete i-autocomplete' "+disabled+"/>";
        html+="<div class=loading rotate'></div>";
        html+="<div class='cancel'></div>";
        html+="<ul class='suggestion-list'></ul>";

		html+='<p class="links">';
		html+=	'<a class="full" data-raw="'+this.model.structure.url+'" href="" target="_blank">'+this.model.structure.linktext+'</a>';
		html+=	'<a class="empty" data-raw="'+this.model.structure.emptyurl+'" href="'+this.model.structure.emptyurl+'" target="_blank">'+this.model.structure.emptylinktext+'</a>';
		html+='</p>';

        jQuery(this.$el).html(html);
        if(fetch)
        {
            var key=this.model.container[this.model.structure.key];
            this.$el.find("input.i-autocomplete").data("key",key);
            var box=this.model.box;
            var self=this;
            GridAjax("typeAheadGetText",[box.getGrid().get("id"),box.getContainer().get("id"),box.getSlot().get("id"),box.getIndex(),this.model.parentpath+this.model.structure.key,key],{
                success_fn:function(data)
                {
                    self.$el.find("input.i-autocomplete").val(data.result);
                }
            });
	        var $linkurl=this.$el.find("a.full");
	        $linkurl.attr('href',$linkurl.data('raw').replace('%',key));
        }
        return this;
    },
    fetchValue:function(){
        return this.$el.find("input.i-autocomplete").data("key");
    },

    keyup:function(e) {
        if(e.which==13)
        {
            this.selectItem(this.$el.find(".suggestion-list li").first());
        }
        else
        {
            if(this.$el.find("input.i-autocomplete").val()==this.old_search_string)return;
            var self=this;
            var search=this.$el.find("input.i-autocomplete").val();
            this.$el.find(".loading").show();
            var box=this.model.box;
            GridAjax("typeAheadSearch",[box.getGrid().get("id"),box.getContainer().get("id"),box.getSlot().get("id"),box.getIndex(),this.model.parentpath+this.model.structure.key,search],{
                success_fn:function(data){
                    self.old_search_string=search;
                    self.$el.find(".suggestion-list").empty();
                    _.each(data.result,function(elem){
                        self.$el.find(".suggestion-list").append(jQuery("<li>"+elem.value+"</li>").attr("data-key",elem.key));
                    });
                    self.$el.find(".loading").hide();
                }
            });
        }
    },
    selectItem:function($item){
        var key=$item.data("key");
        var value=$item.text();
        this.$el.find(".autocomplete-wrapper").addClass("locked");
        this.$el.find("input.i-autocomplete")
            .val(value)
            .attr("disabled","disabled")
            .data("key",key);
        this.$el.find(".suggestion-list").empty();
        var $linkurl=this.$el.find("a.full");
        $linkurl.attr('href',$linkurl.data('raw').replace('%',key));
    },
    listItemSelected:function(e){
        this.selectItem(jQuery(e.srcElement));
    },
    cancelSelection:function(e){
        this.$el.find(".autocomplete-wrapper").removeClass("locked");
        this.$el.find("input.i-autocomplete")
            .data("key","")
            .removeAttr("disabled")
            .val("");
        this.old_search_string="";
    }
});