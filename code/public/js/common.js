var Common = {
    init: function () {
        this.backPreviousPage();
        this.removeItemTr();
        this.removeZeroInput();
    },
    backPreviousPage: function () {
        $(document).on('click', '.form-history-back', function () {
            window.history.back();
        });
    },
    removeItemTr: function () {
        $(document).on('click', '.btn-remove', function () {
            $(this).closest('tr').remove();
        });
    },
    removeZeroInput: function () {
        // Remove 0 when click on input price if the value = 0 else then keep it
        $(document).on( "focus", ".input-number", function() {
            var val = $(this).val();

            if (val == 0) {
                $(this).val('');
            }
        });
        $(document).on( "blur", ".input-number", function() {
            var val = $(this).val();

            if (val == '') {
                $(this).val(0);
            }
        });

    }
}
Common.init();
function addCommas(nStr)
{
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

/**
 * format number to currency: 1,000,000.00
 * @param value
 * @returns {*}
 */
function toCurrency(value)
{
    value = value.toFixed(2);
    value = addCommas(value);

    return value;
}

/**
 * revert currency format to number
 * @param value
 * @returns {*|void|string}
 */
function revertCurrency(value)
{
    return value.replace(new RegExp('\\,', 'g'), '');
}
