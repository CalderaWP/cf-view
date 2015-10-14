# CF View
Front end viewer for Caldera Forms.

Early beta, works but not up to full potential. May or may not become a paid product, or a part of Caldera Forms itself.

Powered by [FooTable](http://fooplugins.github.io/) by our good friends at [FooPlugins](http://fooplugins.com/)

### Usage
* Show all fields of a form:
   * `[cf_view id="CF557dd7b7739ac"]`
* Show certain fields of a form:
   * `[cf_view id="CF557dd7b7739ac" fields="jedi,planet,ship"]`
* Add a link to edit entry:
   * `[cf_view id="CF557dd7b7739ac" editor_id="42"]`
   * Note: `editor_id` must be the ID of a post or page with this form in it be edited.
   * Note: We are likey to make that smarter soon.

### Things Still To Do
* Incorporate all of FooTable's shiny sorting, paging and filtering.
* Load values via AJAX.
* Add a UI for settings.
* Optional mode where only certain fields are shown and detail display opens as a modal like in backend.
* Maybe use Caldera Forms shortcode.
* Maybe start over by outputting backend editor we have now, in the front-end and make both use FooTable for better presentation.


### Copyright, License Etc.
Copyright 2015 Josh Pollock for CalderaWP LLC. License under the terms of the GNU GPL v2+.

