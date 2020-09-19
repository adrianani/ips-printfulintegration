(function ($, _, undefined) {
  "use strict";

  ips.controller.register("printfulintegration.admin.products.list", {
    _ajaxCall: null,
    selectedItems: null,
    _url: null,

    initialize: function () {
      this.on("click", "[data-page]", this.pageClick);
      this.on("submit", '[data-role="pageJump"]', this.pageJump);
      this.on("click", '[data-action="submitSearch"]', this.submitSearch);
      this.on("keydown", "#printfulSearch", this.submitSearch);
      this.on("click", '[data-action="markForImport"]', this.markItemForImport);
      this.on(
        "click",
        '[data-action="unmarkForImport"]',
        this.unmarkItemForImport
      );
      this.on(window, "load", this.checkMarkedProducts);
      this.on("click", '[data-action="process"]', this.submitImport);
      this.on("click", '[data-action="cancel"]', this.cancelImport);

      this.selectedItems = this.scope.find(".ipsPageAction");
    },

    submitImport: function () {
      let items = localStorage.getItem("printfulProducts")
          ? JSON.parse(localStorage.getItem("printfulProducts"))
          : [],
        url =
          this.scope.find("form").attr("action") +
          "&process=" +
          items.join(",");

      localStorage.removeItem("printfulProducts");
      window.location.href = url;
    },

    cancelImport: function () {
      localStorage.removeItem("printfulProducts");

      window.location.href = this.scope.find("form").data("baseurl");
    },

    checkMarkedProducts: function () {
      let items = localStorage.getItem("printfulProducts")
        ? JSON.parse(localStorage.getItem("printfulProducts"))
        : [];

      if (items.length !== 0) {
        items.map(function (val) {
          $(`[data-action="markForImport"][data-product-id="${val}"]`)
            .attr("_title", ips.getString("printful_add_to_store_undo"))
            .attr("data-action", "unmarkForImport")
            .data("action", "unmarkForImport")
            .addClass("ipsButton_negative")
            .removeClass("ipsButton_primary");
        });

        this.selectedItems
          .css("display", "block")
          .find('[data-role="count"]')
          .text(
            ips.pluralize(
              ips.getString("printfulSelectedForImport"),
              items.length
            )
          );
      }
    },

    markItemForImport: function (e) {
      e.preventDefault();
      let id = $(e.currentTarget).data("product-id"),
        items = localStorage.getItem("printfulProducts")
          ? JSON.parse(localStorage.getItem("printfulProducts"))
          : [];

      if (items.indexOf(id) === -1) {
        items.push(id);
        localStorage.setItem("printfulProducts", JSON.stringify(items));
      }

      this.selectedItems
        .css("display", "block")
        .find('[data-role="count"]')
        .text(
          ips.pluralize(
            ips.getString("printfulSelectedForImport"),
            items.length
          )
        );

      ips.ui.tooltip.respond($(e.currentTarget), {}, { type: "mouseleave" });

      $(e.currentTarget)
        .attr("_title", ips.getString("printful_add_to_store_undo"))
        .attr("data-action", "unmarkForImport")
        .data("action", "unmarkForImport")
        .addClass("ipsButton_negative")
        .removeClass("ipsButton_primary");
      ips.ui.tooltip.respond($(e.currentTarget), {}, { type: "mouseenter" });
    },

    unmarkItemForImport: function (e) {
      e.preventDefault();
      let id = $(e.currentTarget).data("product-id"),
        items = localStorage.getItem("printfulProducts")
          ? JSON.parse(localStorage.getItem("printfulProducts"))
          : [];

      items.splice(items.indexOf(id));
      localStorage.setItem("printfulProducts", JSON.stringify(items));

      this.selectedItems
        .find('[data-role="count"]')
        .text(
          ips.pluralize(
            ips.getString("printfulSelectedForImport"),
            items.length
          )
        );

      if (items.length == 0) {
        this.selectedItems.css("display", "none");
      }

      ips.ui.tooltip.respond($(e.currentTarget), {}, { type: "mouseleave" });
      $(e.currentTarget)
        .attr("_title", ips.getString("printful_add_to_store"))
        .attr("data-action", "markForImport")
        .data("action", "markForImport")
        .removeClass("ipsButton_negative")
        .addClass("ipsButton_primary");
      ips.ui.tooltip.respond($(e.currentTarget), {}, { type: "mouseenter" });
    },

    pageClick: function (e) {
      e.preventDefault();

      this.updateView($(e.currentTarget).attr("href"));
    },

    submitSearch: function (e) {
      let keyCode = e.keyCode ?? e.which;

      if (e.type === "keydown" && keyCode == 13) {
        e.preventDefault();
      }

      let input = this.scope.find("#printfulSearch"),
        value = input.val(),
        urlObj = ips.utils.url.getURIObject(this._url);

      if (
        (e.type === "keydown" && keyCode !== 13) ||
        $.trim(value) === urlObj.queryKey.printfulSearch
      ) {
        return;
      }

      if (value.length >= 1 && value.length < 3) {
        input.addClass("ipsField_error");
        return;
      }

      if (input.hasClass("ipsField_error")) {
        input.removeClass("ipsField_error");
      }

      if (!$.trim(value).length) {
        delete urlObj.queryKey.printfulSearch;
      } else {
        urlObj.queryKey.printfulSearch = encodeURI(value);
      }

      this.updateView(ips.utils.url.rebuildUriObject(urlObj));
    },

    pageJump: function (e) {
      e.preventDefault();
      let url = $(e.currentTarget).attr("action"),
        page = $(e.currentTarget).find('input[name="page"]').val();

      url += "&page=" + page;

      this.updateView(url);
    },

    updateView: function (url) {
      let self = this;

      if (this._ajaxCall && _.isFunction(this._ajaxCall.abort)) {
        this._ajaxCall.abort();
      }

      this._setLoading(true);

      this._ajaxCall = ips
        .getAjax()(url)
        .done(function (response) {
          $('[data-role="printfulProducts"]').replaceWith(response.contents);
        })
        .always(function () {
          self._setLoading(false);
        });
    },

    _setLoading: function (state) {
      if (state) {
        $('[data-role="printfulProducts"]')
          .css("height", $('[data-role="printfulProducts"]').height())
          .html("")
          .addClass("ipsLoading");
      } else {
        $('[data-role="printfulProducts"]')
          .css("height", "auto")
          .removeClass("ipsLoading");
      }
    },
  });
})(jQuery, _);
