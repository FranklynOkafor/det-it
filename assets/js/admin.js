jQuery(document).ready(function ($) {
  $(document).on("click", ".detit-trigger", function () {
    const button = $(this);
    const productId = button.data("product-id");
    const responseBox = $("#detit-response");

    if (!productId) return;

    button.prop("disabled", true).text("Processing...");

    $.ajax({
      url: detitData.ajax_url,
      type: "POST",
      data: {
        action: "detit_generate",
        product_id: productId,
        nonce: detitData.nonce,
      },
      success: function (res) {
        console.log(res);

        if (res.success) {
          $("#detit-response").html(
            "<pre>" + JSON.stringify(res.data.context, null, 2) + "</pre>",
          );
        } else {
          responseBox.html(
            '<p style="color:red;">Error processing request</p>',
          );
        }
      },
      error: function () {
        responseBox.html('<p style="color:red;">AJAX error occurred</p>');
      },
      complete: function () {
        button.prop("disabled", false).text("Detail It");
      },
    });
  });
});
