
var Revisions = Backbone.Collection.extend({
	model: Revision,
	initialize: function(spec){
		if(!spec || !spec.grid ||!(spec.grid instanceof Grid )){
			throw "InvalidConstructArgs Revisions need a Grid"
		}
		this.grid = spec.grid;
	},
	getGridID: function(){
		GRID.log(this);
		return this.grid.getGridID();
	},
	sync: function(method, collection, options){
		GridRequest.revisions(collection, options);
	}
});

// type collections
var ContainerTypes = Backbone.Collection.extend({
	model: ContainerType,
	sync: function(method, collection, options){
		GridRequest.containertypes(collection, options);
	}
});
var ReusableContainers = Backbone.Collection.extend({
	model: ContainerType,
	sync: function(method, collection, options){
		GridRequest.reusablecontainers(collection, options);
	}
});
var BoxTypes = Backbone.Collection.extend({
	model: BoxType,
	sync: function(method, collection, options){
		GridRequest.boxtypes(collection, options);
	}
});
var Styles = Backbone.Collection.extend({
	model: StyleType,
	initialize: function(spec){
		if (!spec || !spec.type ) {
            throw "InvalidConstructArgs Styles";
        }
        this.type = spec.type;
	},
	sync: function(method, collection, options){
		GridRequest.styles(collection, options);
	}
});

// element collections
var Containers = Backbone.Collection.extend({
	model: Container,
	move: function(container, to_index){
		var self = this;
		GridRequest.container.update(container,{
			action: "move",
			index: to_index,
			success: function(data){
				self.remove(container);
				self.add(container, {at:to_index});
			}
		});
		return this;
	}
});
var Slots = Backbone.Collection.extend({
	model: Slot
});
var Boxes = Backbone.Collection.extend({
	model: Box
});
