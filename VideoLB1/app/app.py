import sqlite3
from flask import Flask, request, jsonify, session

app = Flask(__name__)
# Ein geheimer Schlüssel ist für Sessions in Flask notwendig
app.secret_key = 'super-geheimes-session-passwort'

# --- DATENBANK-SETUP ---

def init_db():
    conn = sqlite3.connect(':memory:', check_same_thread=False)
    cursor = conn.cursor()
    # Tabellen erstellen
    cursor.execute('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT)')
    cursor.execute('CREATE TABLE invoices (id INTEGER PRIMARY KEY, user_id INTEGER, amount REAL, description TEXT)')
    
    # Testdaten einfügen
    cursor.execute('INSERT INTO users VALUES (1, "Alice")')
    cursor.execute('INSERT INTO users VALUES (2, "Bob")')
    
    # Alice (User 1) gehören die Rechnungen 101 und 102
    cursor.execute('INSERT INTO invoices VALUES (101, 1, 250.00, "Webdesign Service")')
    cursor.execute('INSERT INTO invoices VALUES (102, 1, 89.99, "Server Hosting")')
    # Bob (User 2) gehört die Rechnung 201
    cursor.execute('INSERT INTO invoices VALUES (201, 2, 1500.00, "Geheimes Luxus-Consulting")')
    
    conn.commit()
    return conn

db_conn = init_db()

# --- HILFS-ENDPOINTS FÜR DIE DEMO (Simuliert den Login) ---
@app.route('/login/alice')
def login_alice():
    session['user_id'] = 1
    return "Eingeloggt als Alice (User-ID: 1). Sie darf NUR die Rechnungen 101 und 102 sehen."

@app.route('/login/bob')
def login_bob():
    session['user_id'] = 2
    return "Eingeloggt als Bob (User-ID: 2). Er darf NUR die Rechnung 201 sehen."


# ==============================================================================
# DIE SCHWACHSTELLE (Vulnerable Endpoint)
# ==============================================================================
@app.route('/vulnerable/invoice/<int:invoice_id>')
def get_invoice_vulnerable(invoice_id):
    if 'user_id' not in session:
        return "Bitte zuerst einloggen!", 401
        
    cursor = db_conn.cursor()
    # FEHLER: Die ID wird ungeprüft aus der URL genommen. 
    # Es wird nicht geprüft, ob die Rechnung dem eingeloggten User gehört!
    cursor.execute("SELECT * FROM invoices WHERE id = ?", (invoice_id,))
    invoice = cursor.fetchone()
    
    if invoice:
        return jsonify({"invoice_id": invoice[0], "user_id": invoice[1], "amount": invoice[2], "description": invoice[3]})
    return "Rechnung nicht gefunden", 404



# ==============================================================================
# DIE MASSNAHME (Secure Endpoint)
# ==============================================================================
@app.route('/secure/invoice/<int:invoice_id>')
def get_invoice_secure(invoice_id):
    if 'user_id' not in session:
        return "Bitte zuerst einloggen!", 401
        
    aktueller_user = session['user_id']
    cursor = db_conn.cursor()
    
    # LÖSUNG: Wir koppeln die Abfrage zwingend an die user_id aus der Session!
    cursor.execute("SELECT * FROM invoices WHERE id = ? AND user_id = ?", (invoice_id, aktueller_user))
    invoice = cursor.fetchone()
    
    if invoice:
        return jsonify({"invoice_id": invoice[0], "user_id": invoice[1], "amount": invoice[2], "description": invoice[3]})
    
    # Wenn die ID existiert, aber nicht dem User gehört, liefert die Query nichts zurück.
    return "Zugriff verweigert! Diese Rechnung gehört dir nicht.", 403


if __name__ == '__main__':
    app.run(debug=True, port=5000)