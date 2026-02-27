-- schema
CREATE TABLE IF NOT EXISTS users
(
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT NOT NULL,
    email         TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at    TEXT DEFAULT (DATETIME('now'))
);

CREATE TABLE IF NOT EXISTS clients
(
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    email      TEXT,
    whatsapp   TEXT,
    created_at TEXT DEFAULT (DATETIME('now')),
    updated_at TEXT DEFAULT (DATETIME('now'))
);

CREATE TABLE IF NOT EXISTS sites
(
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id           INTEGER NOT NULL,
    name                TEXT    NOT NULL,
    domain              TEXT,
    creation_cost       REAL    NOT NULL DEFAULT 0,
    current_monthly_fee REAL    NOT NULL DEFAULT 0,
    created_at          TEXT             DEFAULT (DATETIME('now')),
    updated_at          TEXT             DEFAULT (DATETIME('now')),
    FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS plan_history
(
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    site_id        INTEGER NOT NULL,
    amount         REAL    NOT NULL,
    effective_from TEXT    NOT NULL,
    notes          TEXT,
    created_at     TEXT DEFAULT (DATETIME('now')),
    FOREIGN KEY (site_id) REFERENCES sites (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS invoices
(
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    site_id    INTEGER NOT NULL,
    client_id  INTEGER NOT NULL,
    amount     REAL    NOT NULL CHECK (amount > 0),
    due_date   TEXT    NOT NULL,
    status     TEXT    NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'paid', 'overdue', 'canceled')),
    notes      TEXT,
    created_at TEXT             DEFAULT (DATETIME('now')),
    updated_at TEXT             DEFAULT (DATETIME('now')),
    FOREIGN KEY (site_id) REFERENCES sites (id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_invoices_site_due_date
    ON invoices(site_id, due_date);

CREATE TABLE IF NOT EXISTS templates
(
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    code       TEXT    NOT NULL UNIQUE,
    title      TEXT    NOT NULL,
    body       TEXT    NOT NULL,
    active     INTEGER NOT NULL DEFAULT 1,
    updated_at TEXT             DEFAULT (DATETIME('now'))
);

INSERT OR IGNORE
INTO templates (CODE, title, body)
VALUES ('before_due', 'Pré-vencimento','Olá {client_name}! Lembrando que sua mensalidade do site {site_name} vence em {due_date}. Valor: R$ {amount}. Qualquer dúvida, estou à disposição.'),
       ('on_due', 'Dia do vencimento','Oi {client_name}! Hoje ({due_date}) vence a mensalidade do site {site_name}. Valor: R$ {amount}. Obrigado!'),
       ('overdue', 'Vencido','Olá {client_name}. Consta em aberto a mensalidade do site {site_name}, vencida em {due_date}, no valor de R$ {amount}. Pode me confirmar o pagamento?');
