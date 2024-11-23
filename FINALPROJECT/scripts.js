const app = Vue.createApp({
    data() {
        return {
            form: {
                bedrooms: 1,
                bathrooms: 1,
                floors: 1,
                squareFootage: 1000,
                wallMaterial: 'brick',
                roofMaterial: 'asphalt',
                flooringMaterial: 'hardwood',
                features: []
            },
            images: {
                1: 'images/house-1-floor.jpg',
                2: 'images/house-2-floors.jpg',
                3: 'images/house-3-floors.jpg'
            }
        };
    },
    computed: {
        houseImage() {
            return this.images[this.form.floors] || this.images[1];
        },
        totalCost() {
            const { bedrooms, bathrooms, floors, squareFootage, wallMaterial, roofMaterial, flooringMaterial, features } = this.form;

            // Base cost per square foot
            let costPerSqFt = 150;

            // Adjust cost per square foot based on wall material
            if (wallMaterial === 'concrete') {
                costPerSqFt *= 2; // Concrete is 2x the base price
            } else if (wallMaterial === 'brick') {
                costPerSqFt *= 1.5; // Brick is 1.5x the base price
            } else if (wallMaterial === 'wood') {
                costPerSqFt *= 1; // Wood is the base price
            }

            // Adjust for roof material
            costPerSqFt += roofMaterial === 'tile' ? 30 : roofMaterial === 'metal' ? 20 : 10;

            // Adjust for flooring material
            costPerSqFt += flooringMaterial === 'hardwood' ? 25 : flooringMaterial === 'tile' ? 15 : 5;

            let totalCost = squareFootage * costPerSqFt;

            // Add costs for bedrooms, bathrooms, and other basic features
            totalCost += bedrooms * 35000;
            totalCost += bathrooms * 15000;

            // Adjust for number of floors
            if (floors === 2) totalCost *= 2; // Double cost for 2nd floor
            if (floors === 3) totalCost *= 3; // Triple cost for 3rd floor

            // Adjust for additional features
            if (features.includes('garage')) totalCost *= 2; // Double cost for garage
            if (features.includes('garden')) totalCost *= 1.3; // Increase cost by 1.3x for garden
            if (features.includes('pool')) totalCost *= 1.5; // Increase cost by 1.5x for pool

            return totalCost;
        }
    }
});

app.mount('#app');

const app = Vue.createApp({
    data() {
        return {
            user: null,
            savedHouses: []
        };
    },
    methods: {
        async onSignIn(response) {
            // Get user info from Google
            const userInfo = response.credential;
            const payload = JSON.parse(atob(userInfo.split('.')[1]));

            // Save user to backend
            const res = await fetch('/api/auth/google-signin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ google_id: payload.sub, name: payload.name, email: payload.email })
            });
            const data = await res.json();

            if (data.success) {
                this.user = data.user;
            }
        },
        async saveBuild() {
            const buildData = {
                ...this.form,
                total_cost: this.totalCost
            };

            const res = await fetch('/api/build/save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: this.user.id, ...buildData })
            });

            if (res.ok) {
                alert('Build saved successfully!');
            }
        },
        async fetchSavedHouses() {
            const res = await fetch(`/api/build/fetch.php?user_id=${this.user.id}`);
            const data = await res.json();

            if (data.success) {
                this.savedHouses = data.houses;
            }
        }
    }
});

app.mount('#app');



