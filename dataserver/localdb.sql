-- Local database

-- Session and transaction information is stored in a SQLite database. This database should not be shared with other applications.
-- It seems not worth to put these data in MySQL server. Unlike user information, these data should only be mainipulated by Bearweb. Using SQLite only access a file instead of go through a series of MySQL mechanism.
-- Create a directory name "localdb" in the project's root directory (same as index.php) and create a empty file name "bw.db". Give www-data (user of the script) read/write permission of both the db file and the directory. 
-- Use sqlite tool to open this file and execute the following commands to install the database:

-- Session control:
-- Bearweb manage session in this way:
--   Each HTTP request will have a unique ID, which is TransactionID (128-character long base64, plus prefix).
--   Requests from the same client in a period of time will share the same SessionID (128-character long base64, plus prefix).

-- Session:
-- A session is create when the user send the first HTTP request in a period of time, it is used to identify the user during the period of time (HTTP is stateless, so we use session to deal with this).
-- Technically speaking, SessionID is stored on both server-side in this database and on user-side in cookie. A session will be created if the user agent does not submit a valid SessionID (good format, not expired).
-- A session should expire after the user logout (send a special request) or after the user inactive for a long time. A schedualed process should check the LastUsed field to expire sessions.
-- Session is renew everytime client send a request to Bearweb.
-- Expired session cannot be accessed. Request with an expired SessionID should get a new SessionID. This is the idea of "Secure logout".
-- The session is not bind to any user (member) at the beginning. User need to login (send a special request) to bind his/her session to his/her username.
-- Expired Session and all associated data should not be delete. For history record purpose. In another word, do NOT perform DELETE, unless lack of disk space.

CREATE TABLE BW_Session (
	SessionID 	TEXT NOT NULL PRIMARY KEY, 
	CreateTime 	TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, 
	LastUsed 	TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, 
	Expire 		INTEGER NOT NULL DEFAULT 0, 
	Username 	TEXT NOT NULL DEFAULT '', 
	JSKey 		TEXT NOT NULL DEFAULT '', 
	Salt 		TEXT NOT NULL DEFAULT '' 
) WITHOUT ROWID;

CREATE INDEX BW_Session_Alive ON BW_Session (Expire);
CREATE INDEX BW_Session_User ON BW_Session (Username);
CREATE INDEX BW_Session_History ON BW_Session (CreateTime);

-- Transaction:
-- A transaction is a HTTP request. The first step of the server script is to generate this ID.
-- All transactions are associated with sessions. SessionID comes from cookie submited by client; if no or invalid, a new one will be generated and send to client.
-- Logicly, "SessionID" field should has foreign key constraint. Since the front-end server script will check the Session table before write this field, it is not worth to add this constraint in DBMS. 
-- The "Log" field contains a detailed log of this transaction. This can be used for investigation and debug purpose.
-- The "Status" field contains the HTTP code sent to client-size. Can be used to determine the result of the request. This will be empty if the server script encounter error (bug).
-- Old transaction and all associated data should not be delete. For history record purpose. In another word, do NOT perform DELETE, unless lack of disk space.

CREATE TABLE BW_Transaction (
	TransactionID 	TEXT NOT NULL PRIMARY KEY, 
	URL 		TEXT NOT NULL DEFAULT '', 
	RequestTime 	TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, 
	SessionID 	TEXT NOT NULL DEFAULT '', 
	IP 		TEXT NOT NULL DEFAULT '', 
	ExecutionTime 	REAL NOT NULL DEFAULT '', 
	Status 		TEXT NOT NULL DEFAULT '', 
	Log 		TEXT NOT NULL DEFAULT '' 
) WITHOUT ROWID;

CREATE INDEX BW_Transaction_History ON BW_Transaction (RequestTime);
CREATE INDEX BW_Transaction_Session ON BW_Transaction (SessionID);