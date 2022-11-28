$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

$('.form-tag-search span[data-role="remove"]').click(function() {
  $(this).closest('.search-tag').remove();
});