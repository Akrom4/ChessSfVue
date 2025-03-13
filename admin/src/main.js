import "./assets/styles/main.css";

import Aura from "@primeuix/themes/aura";
import Button from "primevue/button";
import PrimeVue from "primevue/config";
import FloatLabel from "primevue/floatlabel";
import InputText from "primevue/inputtext";
import SelectButton from "primevue/selectbutton";
import StyleClass from "primevue/styleclass";
import ToggleSwitch from "primevue/toggleswitch";
import Toolbar from "primevue/toolbar";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import Chip from "primevue/chip";
import Password from "primevue/password";
import MultiSelect from "primevue/multiselect";
import ToastService from 'primevue/toastservice';
import Toast from 'primevue/toast';
import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";

const app = createApp(App);

// First register global plugins
app.use(router);
app.use(ToastService);
app.use(PrimeVue, {
	theme: {
		preset: Aura,
		options: {
			darkModeSelector: ".p-dark",
		},
	},
});

// Then register components
app.component("Button", Button);
app.component("InputText", InputText);
app.component("FloatLabel", FloatLabel);
app.component("Toolbar", Toolbar);
app.component("ToggleSwitch", ToggleSwitch);
app.component("SelectButton", SelectButton);
app.component("Card", Card);
app.component("DataTable", DataTable);
app.component("Column", Column);
app.component("Dialog", Dialog);
app.component("Chip", Chip);
app.component("Password", Password);
app.component("MultiSelect", MultiSelect);
app.component("Toast", Toast);
app.directive("styleclass", StyleClass);

// Finally mount the app
app.mount("#app");
