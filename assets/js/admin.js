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
          var r = res.data.result;
          var html =
            '<p style="color:green;font-weight:bold;">✓ Content generated!</p>' +
            '<p><strong>Title:</strong> ' + r.title + "</p>" +
            '<p><strong>Short Desc:</strong> ' + r.short_description + "</p>" +
            '<p><strong>SEO Title:</strong> ' + r.seo.meta_title + "</p>" +
            '<p><strong>Focus Keyword:</strong> ' + r.seo.focus_keyword + "</p>";
          responseBox.html(html);
        } else {
          responseBox.html(
            '<p style="color:red;">' + (res.data.message || "Error processing request") + "</p>",
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
