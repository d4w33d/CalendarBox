
$(function () {

    $("[data-toggle='popover']").popover({
        html: true,
        container: "body"
    });

    $("[data-toggle='tooltip']").tooltip({
        container: "body"
    });

    $("[data-datetimepicker]").datetimepicker({
        locale: "fr"
    });

    $(".hidden-form .input-value").each(function() {
        var el = $(this),
            par = el.parents(".form-group"),
            inputGroup = par.find(".input-group"),
            formControl = par.find(".form-control");

        el.click(function(e) {
            par.addClass("edit-input");
            formControl.focus();
        });

        formControl.blur(function(e) {
            par.removeClass("edit-input");
        });
    });

    $("table[data-autosize]").each(function() {
        var table = $(this),
            totalWidth = table.width();

        table.find("thead tr th").each(function() {
            var th = $(this);

            th.css({ width: ((th.width() / table.width()) * 100) + "%" });
        });
    });

    $("[data-ajax-form]").each(function() {
        var form = $(this),
            isForm = form[0].tagName === "form";

        if (isForm) {
            form.submit(function(e) {
                e.preventDefault();
            });
        }

        var saveForm = function(input) {
            var modal = $("#loadingModal");

            modal.modal("show");

            var action = form.attr("action")
                    || form.data("form-action"),
                method = form.attr("method")
                    || form.data("form-method")
                    || "get",
                data = isForm
                    ? form.serialize()
                    : form.find(":input").serialize();

            $.ajax({
                url: action,
                type: method,
                data: data,
                dataType: "json",
                success: function(res) {
                    var v = input
                        .parents(".form-group")
                        .find(".input-value .val");

                    if (v.length) {
                        v.text(input.val())
                         .html(v.html().replace(/(\n)/g, "<br>"));

                        var htmlValue = $.trim(v.html());

                        if (!htmlValue) {
                            htmlValue = "&ndash;";
                        }

                        v.html(htmlValue);

                        $("[data-val='" + input.attr("id") + "']")
                            .html(htmlValue);
                    }

                    modal.modal("hide");
                }
            });
        };

        form.find("[data-datetimepicker]").on("dp.change", function(e) {
            saveForm($(e.target).find("input, textarea, select"));
        });

        form
            .find(".form-control, input[type='hidden']")
            .on("change", function(e) {
                saveForm($(this));
            });
    });

    $("[data-days-selector]").each(function() {
        var el = $(this),
            bts = el.find("span");

        bts.each(function() {
            var bt = $(this),
                input = bt.find("input"),
                status = Boolean(parseInt(input.val(), 10));

            var upd = function() {
                bt.removeClass(status ? "off" : "on")
                  .addClass(status ? "on" : "off");

                var val = status ? 1 : 0;

                if (parseInt(input.val(), 10) !== val) {
                    input.val(val).trigger("change");
                }
            };

            upd();

            bt.click(function(e) {
                status = !status;
                upd();
            });
        });
    });

    $("[data-day-event]").each(function() {
        var el = $(this),
            evt = el.data("day-event"),
            siblings = $("[data-day-event='" + evt + "']");

        el.mouseover(function(e) {
            siblings.addClass("hover");
        }).mouseout(function(e) {
            siblings.removeClass("hover");
        });
    });

});
