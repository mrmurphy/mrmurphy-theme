/**
 * Responsive stacked tables.
 *
 * Every data cell gets a `data-label` copied from its column header, and the
 * table gets `.table-stacked`. On small screens (base.css, max-width: 768px)
 * the CSS rebuilds each row as a card whose cells carry their header as a
 * visible label. Tables without a usable header are left untouched and fall
 * back to the horizontal-scroll rule set.
 */
(function () {
  'use strict';

  var STACKED_CLASS = 'table-stacked';
  var WIDE_THRESHOLD = 28; // chars — longer values span the whole card

  function cellText(cell) {
    return (cell.textContent || '').trim();
  }

  function isAllTh(row) {
    return row && row.children.length > 0 && Array.prototype.every.call(
      row.children,
      function (cell) { return cell.tagName === 'TH'; }
    );
  }

  // Definition-style rows: a leading th names the row, one td carries its
  // value. Stackable without any column headers.
  function usesRowTitles(table) {
    return Array.prototype.every.call(table.rows, function (row) {
      return row.children.length === 2
        && row.children[0].tagName === 'TH'
        && row.children[1].tagName === 'TD';
    });
  }

  function stackWithRowTitles(table) {
    Array.prototype.forEach.call(table.rows, function (row) {
      row.children[0].classList.add('table-stack-title');
      var value = row.children[1];
      if (cellText(value).length > WIDE_THRESHOLD) {
        value.classList.add('table-stack-wide');
      }
    });
    table.classList.add(STACKED_CLASS);
  }

  function labelTable(table) {
    if (table.dataset.stackLabeled) {
      return;
    }
    table.dataset.stackLabeled = 'true';

    var headRow = table.querySelector('thead tr');
    var headerIsFirstBodyRow = false;

    if (!headRow) {
      // No thead: a first body row of th cells can serve as the header row...
      var firstRow = table.querySelector('tr');
      // ...or a definition-style table (each row: leading th + values) can
      // stack with the row header as the card title.
      if (firstRow && usesRowTitles(table)) {
        stackWithRowTitles(table);
        return;
      }
      if (firstRow && isAllTh(firstRow)) {
        headRow = firstRow;
        headerIsFirstBodyRow = true;
      } else {
        // No usable header — leave horizontal-scroll fallback in place.
        return;
      }
    }

    var labels = Array.prototype.map.call(headRow.children, function (cell) {
      return cellText(cell);
    });

    if (headerIsFirstBodyRow && headRow) {
      headRow.classList.add('table-stack-header');
    }

    var rows = headerIsFirstBodyRow
      ? Array.prototype.slice.call(table.rows, 1)
      : Array.prototype.slice.call(table.querySelectorAll('tbody tr'));

    rows.forEach(function (row) {
      Array.prototype.forEach.call(row.children, function (cell, i) {
        if (labels[i]) {
          cell.setAttribute('data-label', labels[i]);
        }
        if (i === 0) {
          cell.classList.add('table-stack-title');
        }
        if (cellText(cell).length > WIDE_THRESHOLD) {
          cell.classList.add('table-stack-wide');
        }
      });
    });

    table.classList.add(STACKED_CLASS);
  }

  function run() {
    Array.prototype.forEach.call(document.querySelectorAll('table'), labelTable);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();
