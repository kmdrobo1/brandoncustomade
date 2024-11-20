let listProductHTML = document.querySelector('.listProduct');
let listCartHTML = document.querySelector('.listCart');
let iconCart = document.querySelector('.icon-cart');
let iconCartSpan = document.querySelector('.icon-cart span');
let body = document.querySelector('body');
let closeCart = document.querySelector('.close');

// Such- und Filterfelder
let searchInput = document.querySelector('#search');
let filterMinPrice = document.querySelector('#min_price');
let filterMaxPrice = document.querySelector('#max_price');
let categorySelect = document.querySelector('#category');

let products = [];
let cart = JSON.parse(localStorage.getItem('cart')) || []; // Warenkorb aus LocalStorage

iconCart.addEventListener('click', () => {
    body.classList.toggle('showCart');
});
closeCart.addEventListener('click', () => {
    body.classList.toggle('showCart');
});

// Produkte abrufen und filtern
const fetchProducts = () => {
    let searchQuery = searchInput.value;
    let minPrice = filterMinPrice.value || 0;
    let maxPrice = filterMaxPrice.value || 999999;
    let category = categorySelect.value;

    fetch(`ErsatzteileLaden.php?search=${searchQuery}&min_price=${minPrice}&max_price=${maxPrice}&category=${category}`)
    .then(response => response.json())
    .then(data => {
        products = data;
        addDataToHTML();
    })
    .catch(error => console.error('Fehler beim Abrufen der Produkte:', error));
};

// Produkte in HTML anzeigen und Verfügbarkeit kennzeichnen
const addDataToHTML = () => {
    listProductHTML.innerHTML = '';
    if (products.length > 0) {
        products.forEach(product => {
            let newProduct = document.createElement('div');
            newProduct.dataset.id = product.id;
            newProduct.dataset.category = categorySelect.value;
            newProduct.classList.add('item');

            // Verfügbarkeitsanzeige und Button-Zustand
            let availabilityText = product.Menge_Einheiten > 0 ? "In den Warenkorb" : "Nicht verfügbar";
            let buttonState = product.Menge_Einheiten > 0 ? "" : "disabled";

            newProduct.innerHTML = 
            `<img src="data:image/jpeg;base64,${product.Grafik}" alt="">
             <h2>${product.Art_Beschreibung}</h2>
             <div class="price">€ ${product.Preis}</div>
             <button class="addCart" ${buttonState}>${availabilityText}</button>`;
            listProductHTML.appendChild(newProduct);
        });
    }
};

// Produkt zum Warenkorb hinzufügen
listProductHTML.addEventListener('click', (event) => {
    let positionClick = event.target;
    if (positionClick.classList.contains('addCart') && !positionClick.disabled) {
        let productElement = positionClick.parentElement;
        let product_id = productElement.dataset.id;
        let product_category = productElement.dataset.category;
        let product_name = productElement.querySelector('h2').innerText;
        let product_price = parseFloat(productElement.querySelector('.price').innerText.replace('€', '').trim());
        let product_image = productElement.querySelector('img').src;
        let product_stock = products.find(p => p.id == product_id).Menge_Einheiten;

        addToCart({
            product_id: product_id,
            category: product_category,
            name: product_name,
            price: product_price,
            image: product_image,
            stock: product_stock
        });
    }
});

// Funktion zum Hinzufügen zum Warenkorb
const addToCart = (product) => {
    let positionThisProductInCart = cart.findIndex((item) => item.product_id == product.product_id && item.category == product.category);

    if (positionThisProductInCart < 0) {
        if (product.stock > 0) {
            cart.push({ ...product, quantity: 1 });
        } else {
            alert("Dieser Artikel ist momentan nicht verfügbar.");
        }
    } else {
        let currentQuantity = cart[positionThisProductInCart].quantity;
        if (currentQuantity < product.stock) {
            cart[positionThisProductInCart].quantity += 1;
        } else {
            alert("Maximale verfügbare Menge erreicht.");
        }
    }

    addCartToHTML();
    addCartToMemory();
};

// Warenkorb in LocalStorage speichern
const addCartToMemory = () => {
    localStorage.setItem('cart', JSON.stringify(cart));
};

// Warenkorb im HTML anzeigen
const addCartToHTML = () => {
    listCartHTML.innerHTML = '';
    let totalQuantity = 0;
    let totalSum = 0;

    if (cart.length > 0) {
        cart.forEach(item => {
            totalQuantity += item.quantity;
            let newItem = document.createElement('div');
            newItem.classList.add('item');
            newItem.dataset.id = item.product_id;
            newItem.dataset.category = item.category;

            totalSum += item.price * item.quantity;

            listCartHTML.appendChild(newItem);
            newItem.innerHTML = `
            <div class="image">
                <img src="${item.image}">
            </div>
            <div class="name">${item.name}</div>
            <div class="totalPrice">€ ${(item.price * item.quantity).toFixed(2)}</div>
            <div class="quantity">
                <span class="minus"><</span>
                <span>${item.quantity}</span>
                <span class="plus">></span>
            </div>`;
        });
    }

    document.getElementById('totalAmount').innerText = `€ ${totalSum.toFixed(2)}`;
    iconCartSpan.innerText = totalQuantity;
};

// Menge im Warenkorb ändern
listCartHTML.addEventListener('click', (event) => {
    let positionClick = event.target;
    if (positionClick.classList.contains('minus') || positionClick.classList.contains('plus')) {
        let product_id = positionClick.parentElement.parentElement.dataset.id;
        let product_category = positionClick.parentElement.parentElement.dataset.category;
        let type = positionClick.classList.contains('plus') ? 'plus' : 'minus';

        changeQuantityCart(product_id, product_category, type);
    }
});

// Warenkorbmenge ändern
const changeQuantityCart = (product_id, product_category, type) => {
    let itemIndex = cart.findIndex(item => item.product_id == product_id && item.category == product_category);

    if (itemIndex >= 0) {
        let item = cart[itemIndex];
        if (type === 'plus') {
            if (item.quantity < item.stock) {
                item.quantity += 1;
            } else {
                alert("Maximale verfügbare Menge erreicht.");
            }
        } else {
            if (item.quantity > 1) {
                item.quantity -= 1;
            } else {
                cart.splice(itemIndex, 1);
            }
        }
    }
    
    addCartToHTML();
    addCartToMemory();
};

// Eventlistener für Filter
searchInput.addEventListener('input', fetchProducts);
filterMinPrice.addEventListener('input', fetchProducts);
filterMaxPrice.addEventListener('input', fetchProducts);
categorySelect.addEventListener('change', fetchProducts);

// App initialisieren
const initApp = () => {
    fetchProducts();
    addCartToHTML();
};

initApp();

// "Kaufen"-Button definieren (falls noch nicht geschehen)
let checkOutButton = document.querySelector('.checkOut');

// Funktion zum Aktualisieren des Bestands nach Kauf
const purchaseItems = () => {
    if (cart.length === 0) {
        alert("Ihr Warenkorb ist leer.");
        return;
    }

    // Warenkorb-Daten an das Backend senden
    fetch('ErsatzteileAktualisieren.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cart: cart })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Vielen Dank für Ihren Einkauf! Ihre Bestellung wurde erfolgreich aufgegeben.");
            cart = [];             // Warenkorb leeren
            addCartToMemory();     // Leeren Warenkorb in localStorage speichern
            addCartToHTML();       // Warenkorb-HTML aktualisieren
            fetchProducts();       // Produktliste auf der Hauptseite neu laden
        } else {
            alert("Fehler beim Kauf: " + (data.errors ? data.errors.join(', ') : data.message));
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Beim Kauf ist ein Fehler aufgetreten.");
    });
};

// Event-Listener für den "Kaufen"-Button
checkOutButton.addEventListener('click', purchaseItems);
