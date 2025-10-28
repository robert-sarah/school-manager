import sys
from PyQt5.QtWidgets import QApplication, QWidget, QVBoxLayout, QHBoxLayout, QPushButton, QTextEdit, QFileDialog, QLabel, QMessageBox
from PyQt5.QtCore import Qt

class MainWindow(QWidget):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("Prévisualiseur de Fichiers PHP")
        self.resize(800, 600)  # Taille initiale responsive

        # Layout principal
        self.layout = QVBoxLayout(self)
        self.layout.setContentsMargins(10, 10, 10, 10)
        self.layout.setSpacing(10)

        # Label d'instructions
        self.label = QLabel("Sélectionnez un fichier PHP pour prévisualiser son contenu.")
        self.label.setAlignment(Qt.AlignCenter)
        self.layout.addWidget(self.label)

        # Bouton pour sélectionner le fichier
        self.select_button = QPushButton("Sélectionner Fichier PHP")
        self.select_button.clicked.connect(self.select_file)
        self.layout.addWidget(self.select_button)

        # Zone d'affichage du contenu
        self.text_edit = QTextEdit()
        self.text_edit.setReadOnly(True)
        self.text_edit.setLineWrapMode(QTextEdit.NoWrap)  # Pas de wrap pour code
        self.layout.addWidget(self.text_edit, stretch=1)  # Prend l'espace disponible

        # Layout pour boutons Ouvrir, Rafraîchir, Fermer
        button_layout = QHBoxLayout()
        self.open_button = QPushButton("Ouvrir")
        self.open_button.clicked.connect(self.load_content)
        self.open_button.setEnabled(False)  # Désactivé jusqu'à sélection
        button_layout.addWidget(self.open_button)

        self.refresh_button = QPushButton("Rafraîchir")
        self.refresh_button.clicked.connect(self.load_content)
        self.refresh_button.setEnabled(False)
        button_layout.addWidget(self.refresh_button)

        self.close_button = QPushButton("Fermer")
        self.close_button.clicked.connect(self.close_file)
        button_layout.addWidget(self.close_button)

        self.layout.addLayout(button_layout)

        # Variable pour stocker le chemin du fichier sélectionné
        self.file_path = None

    def select_file(self):
        file_path, _ = QFileDialog.getOpenFileName(self, "Sélectionner un fichier PHP", "", "PHP Files (*.php);;All Files (*)")
        if file_path:
            self.file_path = file_path
            self.label.setText(f"Fichier sélectionné: {file_path}")
            self.open_button.setEnabled(True)
            self.refresh_button.setEnabled(True)
            self.load_content()  # Charger automatiquement après sélection

    def load_content(self):
        if not self.file_path:
            return

        try:
            with open(self.file_path, 'r', encoding='utf-8') as file:
                content = file.read()
            self.text_edit.setPlainText(content)
            self.label.setText(f"Contenu chargé: {self.file_path}")
        except FileNotFoundError:
            QMessageBox.warning(self, "Erreur", "Fichier non trouvé.")
            self.file_path = None
            self.open_button.setEnabled(False)
            self.refresh_button.setEnabled(False)
        except Exception as e:
            QMessageBox.warning(self, "Erreur", f"Erreur lors du chargement: {str(e)}")

    def close_file(self):
        self.text_edit.clear()
        self.file_path = None
        self.label.setText("Sélectionnez un fichier PHP pour prévisualiser son contenu.")
        self.open_button.setEnabled(False)
        self.refresh_button.setEnabled(False)

if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = MainWindow()
    window.show()
    sys.exit(app.exec_())