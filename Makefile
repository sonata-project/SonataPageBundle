test:
	phpunit
	cd Resources/doc && sphinx-build -W -b html -d _build/doctrees . _build/html

assets:
	cd Resources/assets_src && npm install
	cd Resources/assets_src && gulp