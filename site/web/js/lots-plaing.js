$(() => {
    // часы на кнопке ..
    let timerButton = $('button.server-time');
    let timerID;
    let tim = timerButton.data('time');

    let timer = function() {
        // время на кнопке..

        tim += 1000;
        let d = new Date(tim);
        timerButton.text(d.toLocaleTimeString('ru-RU'));

        $('.plaing-list .lot-item').each((ind, el) => {
            let jEl = $(el);
            let data = jEl.data();
            let upPrice = jEl.find('.up-price');

            if (data.dprice) {
                upPrice.html((data.co * data.dprice).toFixed(2) + 'р.');
            }
            if (data.co > 0 && data.lastdate) {
                //d = new Date(data.lastdate);
                let scale = jEl.find('.time-lost .scale');
                if (data.closed && !upPrice.hasClass('closed')) {
                    upPrice.addClass('closed');
                }
                scale.css('width', ( (data.dtime - (tim - data.lastdate) / 1000) / data.dtime * 100) + '%');
            }
        });
    }


    let container = $('.plaing-list');
    let coStakes = 0;

    // сделать стаавку ...
    container.on('click', '.up-price', e => {
        let el = $(e.target).parent();
        if (el.data('closed') == 1) {
            console.warn('Торги завершены');
            return ;
        }
        let id = el.data('id');
        $.post('', {action: 'put-stake', on: id}, ret => {
            el.data(ret.data);
        });
    });


    container.on('data-update', (e, coS = -1) => {
        // return;
        $.post('', {action: 'upd', syncs: coS}, (ret) => {

            let coS2 = 0;
            for (let i = 0; i < ret.data.length; i++) {
                coS2 += ret.data[i].co;
                let el = container.find('.item-' + ret.data[i].id);

                ret.data[i].dprice = parseFloat(ret.data[i].dprice);
                if (!el.length) {
                    let tag = $('<div>');
                    tag.addClass('lot-item item-' + ret.data[i].id);
                    tag.data({id: ret.data[i].id});
                    let line = ` ${ret.data[i].name} (<span class="up-price" title="Поднять цену (сделать ставку)"></span>)
                        <div class="time-lost" title="До окончания торгов осталось ..."><div class="scale" ></div></div>`
                    tag.html(line);
                    container.append(tag);
                    el = container.find('.item-' + ret.data[i].id);
                    //  добавить элемент
                }
                el.data(ret.data[i]);
            }
            container.trigger('data-update', [coS2]);
        }).fail(() => {
            container.trigger('data-update', [coS]);
        });

    });

    // обнова данных ...

    container.trigger('data-update');
    /*$('.plaing-list .lot-item').on('update-data', (e) => {
        $.post('', {action: 'get-data', ids: [$(e.target).data('id')]}, ret => {
            console.log(ret);
        });
    });*/

    setInterval(timer, 1000);
});