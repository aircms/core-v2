/**
 * Парсит CSV-строку в массив строк, каждая — массив полей.
 * Поддерживает:
 *  - поля в кавычках, включая запятые и переводы строк внутри
 *  - экранирование кавычек как ""
 *  - настраиваемый разделитель (по умолчанию запятая)
 */
function parseCSV(csvText, {delimiter = ',', hasHeader = true} = {}) {
  const rows = [];
  let cur = '';
  let inQuotes = false;
  let row = [];
  for (let i = 0; i < csvText.length; i++) {
    const ch = csvText[i];
    const next = csvText[i + 1];

    if (inQuotes) {
      if (ch === '"' && next === '"') {
        // Экранированная кавычка
        cur += '"';
        i++; // пропустить второй
      } else if (ch === '"') {
        inQuotes = false;
      } else {
        cur += ch;
      }
    } else {
      if (ch === '"') {
        inQuotes = true;
      } else if (ch === delimiter) {
        row.push(cur);
        cur = '';
      } else if (ch === '\r') {
        // Игнорировать, подождём \n или конец
      } else if (ch === '\n') {
        row.push(cur);
        rows.push(row);
        row = [];
        cur = '';
      } else {
        cur += ch;
      }
    }
  }
  // Последнее поле/строка
  if (inQuotes) {
    // Неправильный CSV: не закрыты кавычки. Всё же добавим.
  }
  row.push(cur);
  if (row.length > 1 || row[0] !== '' || csvText.endsWith(delimiter)) {
    rows.push(row);
  }

  if (hasHeader && rows.length > 0) {
    const header = rows.shift();
    return {header, rows};
  }
  return {header: null, rows};
}

/**
 * Преобразует результат parseCSV в <table>.
 * Опции:
 *  - classes: строка классов для <table>
 *  - includeHeader: отображать ли заголовок, если он есть
 */
function csvToTable(csvText, {
  delimiter = ',',
  hasHeader = true,
  includeHeader = true,
  classes = ''
} = {}) {
  const {header, rows} = parseCSV(csvText, {delimiter, hasHeader});
  const table = document.createElement('table');
  if (classes) table.className = classes;

  if (header && includeHeader) {
    const thead = document.createElement('thead');
    const tr = document.createElement('tr');
    header.forEach(h => {
      const th = document.createElement('th');
      th.textContent = h;
      tr.appendChild(th);
    });
    thead.appendChild(tr);
    table.appendChild(thead);
  }

  const tbody = document.createElement('tbody');
  rows.forEach(r => {
    const tr = document.createElement('tr');
    r.forEach(cell => {
      const td = document.createElement('td');
      td.textContent = cell;
      tr.appendChild(td);
    });
    tbody.appendChild(tr);
  });
  table.appendChild(tbody);
  return table.outerHTML;
}
