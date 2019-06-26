Vue.component("action-menu", {
	data: function() {
		return {
			current_value: typeof this.actions[0].value !== "undefined" ? this.actions[0].value : this.actions[0].title,
			current_title: this.actions[0].title
		};
	},
	props: ["actions", "buttons", "base_path", "id"],
	template:
		`<div class="action_menu">
			<div class="action_menu_default">
				<a v-if="!buttons" class="action_menu_label" :href="compute_action_url(actions[0])">{{ current_title }}</a>
				<span v-else class="action_menu_label">{{ current_title }}</span>
				<button v-on:click.stop="open" class="action_menu_trigger" type="button"><icon wrapper="action_menu" icon="arrow_drop_down"></icon></button>
			</div>
			<ul class="action_menu_dropdown">
				<li v-for="action in actions" class="action_menu_item">
					<a v-if="!buttons" class="action_menu_link" :href="compute_action_url(action)">{{ action.title }}</a>
					<button v-else v-on:click="change" class="action_menu_link" :data-value="action.value">{{ action.title }}</button>
				</li>
			</ul>
		</div>`,
	methods: {
		change: function(ev) {
			let $target = $(ev.target);

			this.current_title = $target.text();
			this.current_value = $target.data("value");
			this.$parent.$emit("action_menu_change", { id: this.id, value: this.current_value });
		},

		close: function() {
			$(this.$el).find(".action_menu_dropdown").removeClass("action_menu_dropdown_active");
		},

		compute_action_url: function(action) {
			if (action.url) {
				return action.url;
			}

			return this.base_path + action.route + "/" + this.id + "/";
		},

		open: function() {
			// Close any existing menus that are open
			$(window).off("click");
			$(".actiom_menu_dropdown").removeClass("action_menu_dropdown_active");

			// Open this menu
			$(window).on("click", this.close);
			$(this.$el).find(".action_menu_dropdown").addClass("action_menu_dropdown_active");
		}
	}
});