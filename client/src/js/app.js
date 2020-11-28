'use strict';

import '../scss/app.scss';

/* Select sortable */
jQuery.entwine('ss', ($) => {
  var sortableFieldClass = '.TreeDropdownField.treemultiselectfieldsortable'; //.treedropdownfield.sortable

  $(`${sortableFieldClass  } .Select-value`).entwine({
    onmatch() {
      this._super();

      var _this = this;
      var $this = $(this);
      var $parent = $this.parents(sortableFieldClass);

      if (!$parent.hasClass('sort-vals-ready')) {
        var schema = $parent.data('schema');
        var name = schema['name'];
        var vals = $(`input[name="${  name  }"]`)
          .val()
          .split(',');
        var $container = $parent.find('.Select-multi-value-wrapper');

        // setup data vals
        $parent.find('.Select-value').each((i, el) => {
          $(el).attr('data-val', vals[i]);
        });

        setTimeout(() => {
          // reorder items
          var i = 0;
          var vals = schema['data']['sort_order'].split(',');

          var $prevEl;
          vals.forEach((id) => {
            var $el = $parent.find(
              `.Select-value[data-val="${  id  }"]`,
            );
            if (i === 0) {
              $container.prepend($el);
            } else {
              $el.insertAfter($prevEl);
            }

            $prevEl = $el;

            i++;
          });

          _this._storeFieldVal();
        }, 1000);

        $parent.addClass('sort-vals-ready');
      }

      var $this = $(this);
      $this.prepend('<b class="prev">&lt;</b>');
      $this.append('<b class="next">&gt;</b>');
    },
    onclick() {
      console.log('Select sortable: value click');
      this._super();

      var $this = $(this);

      $this
        .parents('.Select-multi-value-wrapper')
        .find('.Select-value')
        .removeClass('active');

      if (!$this.hasClass('active')) {
        $this.addClass('active');
      }
    },
  });

  $(
    `${sortableFieldClass 
    } .Select-value .prev` +
			`,${ 
			  sortableFieldClass 
			} .Select-value .next`,
  ).entwine({
    onmatch() {
      this._super();
    },
    onclick() {
      console.log('Select sortable: prev/next click');

      var $this = $(this);
      var $val = $this.parents('.Select-value');

      // hide menu
      $this.parents('.Select').removeClass('is-open');
      var $menu = $this.parents('.Select').find('.Select-menu-outer');
      if ($menu.length) {
        $menu.hide();
      }

      // move prev
      if ($this.hasClass('prev')) {
        $val.insertBefore($val.prev('.Select-value'));
      }

      // move next
      if ($this.hasClass('next')) {
        $val.insertAfter($val.next('.Select-value'));
      }

      this._saveOrder();
    },

    _storeFieldVal() {
      var $this = $(this);
      var $parent = $this.parents(sortableFieldClass);
      var schema = $parent.data('schema');
      var name = schema['name'];

      var vals = [];
      $parent.find('.Select-value').each((i, el) => {
        vals[i] = $(el).attr('data-val');
      });

      $(`input[name="${  name  }"]`).val(vals.toString());
    },

    _saveOrder() {
      this._storeFieldVal();

      var $this = $(this);
      var $parent = $this.parents(sortableFieldClass);
      var schema = $parent.data('schema');
      var url = schema['data']['url_sort'];

      var name = schema['name'];

      var vals = $(`input[name="${  name  }"]`)
        .val()
        .split(',');
      var data = {};
      data[name] = vals;

      $.ajax({
        headers: { 'X-Pjax': 'CurrentField' },
        type: 'POST',
        url,
        data,
        success: function (response) {
          console.log(response);
        },
        error: function (e) {
          alert(
            ss.i18n._t(
              'Admin.ERRORINTRANSACTION',
              'An error occured while fetching data from the server\n Please try again later.',
            ),
          );
        },
      });
    },
  });
});
