
import App from './components/App'

import axios from 'axios';

window.axios = 'axios';

export default{
    mode: 'history',//How to record each page change (using brower history)

    linkActiveClass: 'font-bold',//Give all active links bold font (check vueRouter documentation)
    routes: [
        {
            path: '/',//Homepage
            component: App//Name of component that should be loaded for homepage
        }
    ]
}