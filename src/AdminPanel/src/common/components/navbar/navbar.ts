import {autoinject} from 'aurelia-dependency-injection';
import {Router} from "aurelia-router";

@autoinject
export class Navbar {
    constructor(private router: Router) {
    }
}
