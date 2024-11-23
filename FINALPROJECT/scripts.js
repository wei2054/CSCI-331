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
            },
            user: null,
            savedHouses: []
        };
    },
    computed: {
        houseImage() {
            return this.images[this.form.floors] || this.images[1];
        },
        totalCost() {
            const { bedrooms, bathrooms, floors, squareFootage, wallMaterial, roofMaterial, flooringMaterial, features } = this.form;
            let costPerSqFt = 150;

            if (wallMaterial === 'concrete') costPerSqFt *= 2;
            else if (wallMaterial === 'brick') costPerSqFt *= 1.5;

            costPerSqFt += roofMaterial === 'tile' ? 30 : roofMaterial === 'metal' ? 20 : 10;
            costPerSqFt += flooringMaterial === 'hardwood' ? 25 : flooringMaterial === 'tile' ? 15 : 5;

            let totalCost = squareFootage * costPerSqFt;
            totalCost += bedrooms * 35000 + bathrooms * 15000;

            if (floors === 2) totalCost *= 2;
            if (floors === 3) totalCost *= 3;
            if (features.includes('garage')) totalCost *= 2;
            if (features.includes('garden')) totalCost *= 1.3;
            if (features.includes('pool')) totalCost *= 1.5;

            return totalCost;
        }
    },
    methods: {
        async onSignIn(response) {
            const payload = JSON.parse(atob(response.credential.split('.')[1]));
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
            const buildData = { ...this.form, total_cost: this.totalCost };
            const res = await fetch('/api/build/save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: this.user.id, ...buildData })
            });

            if (res.ok) alert('Build saved successfully!');
        },
        async fetchSavedHouses() {
            const res = await fetch(`/api/build/fetch.php?user_id=${this.user.id}`);
            const data = await res.json();

            if (data.success) {
                this.savedHouses = data.houses.map(house => ({
                    ...house,
                    image: house.image || `images/house-${house.floors}-floors.jpg`
                }));
            }
        }
    },
    mounted() {
        if (this.user) {
            this.fetchSavedHouses();
        }
    }
});

app.mount('#app');
