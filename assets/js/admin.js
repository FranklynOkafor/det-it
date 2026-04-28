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
        if (res.success) {
          responseBox.html(
            '<p style="color:green;">' + res.data.message + "</p>",
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
