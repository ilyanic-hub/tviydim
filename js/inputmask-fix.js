$('form').on('submit', function(e){
    let number = $(this).find('input[name=phone]').val(),
        pre = /_+/;
        
    if (!!number.match(pre)){
        e.preventDefault();
    }
})