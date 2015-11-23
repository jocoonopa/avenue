/**
 * 盤點
 */
var StockChecker = function () {
    return this.setView().initModel().updateView().bindInput().loadStatus();
};

StockChecker.prototype.setSumNum = function () {
    $('span.has_checked').text($('.be-checked-list').find('tr').length);
    this.$notFound.text($('.to-check-list').find('tr').not('.hidden').length);

    return this;
};

StockChecker.prototype.setView = function () {
    this.$table = $('table.to-check-list');
    this.$input = $('input');
    this.$notHere = $('.not_here');
    this.$notFound = $('.not_found');
    
    return this;
};

StockChecker.prototype.initModel = function () {
    this.notfound = this.$table.find('tbody').find('tr').length;
    this.nothere = 0;
    this._class = false;
    
    return this;
};

StockChecker.prototype.updateView = function () {
    this.$notHere.text(this.nothere);
    this.$notFound.text(this.notfound);
    
    return this;
};

StockChecker.prototype.reduce = function () {
    if (this.$target.hasClass('hidden')) {
        if (!this.isSilent) {
            document.getElementById('alert-repeat').play();
        }   

        return this;
    }

    this.$target.addClass('hidden');

    $('table.be-checked-list').prepend(this.genBeCheckedListTd(this.$target));
    this.setSumNum();
    
    if (!this.isSilent) {
        document.getElementById('alert-ok').play();
    }

    return this;
};

StockChecker.prototype.genBeCheckedListTd = function ($e) {
    $a = $e.find('a');

    return $('<tr class="success"><td class="' + $a.text() + ' target">' + $a.text() + '</td><td><i class="cancel-checked glyphicon glyphicon-share-alt"></i></td></tr>');
}

StockChecker.prototype.reverseReduce = function ($e) {
    var sn = $e.closest('tr').find('.target').text();

    if (confirm('確定要將' + sn + '還原成未盤點狀態嘛?')) {
        $('.to-check-list').find('tr.' + sn).removeClass('hidden');

        $e.closest('tr').remove();

        this.setSumNum();
    }   
};

StockChecker.prototype.addNotHere = function (val) {
    var self = this;
    val = val || self._class;

    $.getJSON(Routing.generate('fix_nothere', {sn: self._class}), {}, function(json, textStatus) {
        if ('1|ok' === json.status) {
            self.reduce().clearInput();
        } else {
            self.nothere ++;
    
            self.$notHere.text(self.nothere);
            
            if (!self.isSilent) {
                document.getElementById('alert-nothere').play();
            }

            self.$table.find('tbody').prepend('<tr class="danger"><td class="remove-my-parent">x</td><td>'+ val +'</td><td></td><td></td></tr>');
        }
    });

    return this;
};

/**
 * 移除有問題的, 並且更改有問題顯示的統計數字
 * 
 * @param  {jQueryElement} $e 
 * @return {[type]}    [description]
 */
StockChecker.prototype.removeNotHere = function ($e) {
    this.nothere --;
    this.$notHere.text(this.nothere);

    $e.closest('tr').remove();

    return this;
};

StockChecker.prototype.resolve = function () {
    return (0 < this.$target.length) ? '-' : '+';
};

StockChecker.prototype.setTarget = function (val) {
    this._class = val;
    this.$target = $('.' + this._class);
    
    return this;
};

StockChecker.prototype.clearInput = function () {
    this.$input.val('');
    
    return this;
};

StockChecker.prototype.bindInput = function () {
    var self = this;

    this.$input.keypress(function () {
        if (13 === event.keyCode) {
            return ('-' === self.setTarget($(this).val().toUpperCase()).resolve()) ? 
                self.reduce().clearInput() : 
                self.addNotHere().clearInput();
        }

        if (20 <= $(this).val().length) {
            if (!self.isSilent) {
                document.getElementById('alert-long').play();
            }

            alert('條碼可能有問題喔!');
        }
    });

    $(document).on('click', 'td.remove-my-parent', function () {
        return self.removeNotHere($(this));
    });

    $(document).on('click', 'i.cancel-checked', function () {
        return self.reverseReduce($(this));
    });

    $('#save-record').click(function () {
        self.saveStatus();
    });

    $('#trash-record').click(function () {
        self.removeStatus();
    });

    return this;
};

StockChecker.prototype.saveStatus = function () {
    var $trs = $('table.be-checked-list').find('tr');
    var container = [];

    $trs.each(function () {
        container.push($(this).find('td').text());
    });

    localStorage.setItem('wordStatus', JSON.stringify(container));

    var $trs = $('tr.danger');
    container = [];
    
    $trs.each(function () {
        container.push($(this).find('td').eq(1).text());
    });

    localStorage.setItem('dangerStatus', JSON.stringify(container));

    // Sync Server Side Data
    return this.syncData();
};

StockChecker.prototype.removeStatus = function () {
    localStorage.removeItem('wordStatus');
    localStorage.removeItem('dangerStatus');

    // Sync Server Side Data
    return this.syncData();
};

StockChecker.prototype.isNotNull = function (obj) {
    return (obj && 0 < obj.length);
};

StockChecker.prototype.loadStatus = function () {
    var self = this;
    var wordStatus = [];//JSON.parse(localStorage.getItem('wordStatus'));
    var dangerStatus = [];//JSON.parse(localStorage.getItem('dangerStatus'));
    var serverStorage = null;

    $.blockUI();
    var request = $.getJSON(Routing.generate('stock_check_load'), function (data) {
        serverStorage = data;

        self.isSilent = true;

        wordStatus = (self.isNotNull(wordStatus)) ? wordStatus : serverStorage.wordStatus;
        dangerStatus = (self.isNotNull(dangerStatus)) ? dangerStatus : serverStorage.dangerStatus;

        wordStatus = (Array.isArray(wordStatus)) ? wordStatus : $.parseJSON(wordStatus);
        dangerStatus = (Array.isArray(dangerStatus)) ? dangerStatus : $.parseJSON(dangerStatus);

        if (self.isNotNull(wordStatus)) {
            for (var i in wordStatus) {
                if (0 === wordStatus[i].length) {
                    continue;
                }

                self.setTarget(wordStatus[i]);

                if (0 === self.$target.length) {
                    continue;
                }

                self.reduce();
            }
        }

        if (self.isNotNull(dangerStatus)) {
            for (var i in dangerStatus) {
                if (0 === dangerStatus[i].length) {
                    continue;
                }
                
                self.setTarget(dangerStatus[i]).addNotHere(dangerStatus[i]);
            }
        }

        self.isSilent = false;
        $.unblockUI();
    });

    return this;
};

StockChecker.prototype.syncData = function () {
    var putData = {
        'wordStatus': localStorage.getItem('wordStatus'),
        'dangerStatus': localStorage.getItem('dangerStatus')
    }; 

    var request = $.ajax({
        method: 'PUT',
        url: Routing.generate('stock_check_update'),
        beforeSend: function(xhr) {
            $.blockUI();
        },
        data: {"content": JSON.stringify(putData)},
        dataType: 'json'
    });
    
    request.done(function(msg) {
        $.unblockUI();
        alert('儲存狀態完成');
    });
    
    request.fail(function () {
        $.unblockUI();
        alert('儲存狀態失敗');
    });

    return this;
};