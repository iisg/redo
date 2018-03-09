import {autoinject} from 'aurelia-dependency-injection';
import {Router} from "aurelia-router";

@autoinject
export class TopBar {
    constructor(private router: Router) {
    }
}
