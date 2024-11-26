import { NgModule } from "@angular/core";
import { BrowserModule } from "@angular/platform-browser";
import { ReactiveFormsModule } from "@angular/forms";
import { HttpClientModule } from "@angular/common/http";
import { ToastrModule } from "ngx-toastr";

import { AppComponent } from "./app.component";
import { LoginComponent } from "./components/login/login.component";
import { WelcomeComponent } from "./components/welcome/welcome.component";
import { UserService } from "./services/users.service";


@NgModule({
    imports: [
        BrowserModule,
        ReactiveFormsModule,
        HttpClientModule,
        ToastrModule,
        LoginComponent,
        WelcomeComponent,
        AppComponent
    ],
    providers: [UserService],
    bootstrap: []
})

export class AppModule { }