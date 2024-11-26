import { Component } from '@angular/core';
import { RelojComponent } from '../reloj/reloj.component';
import {DashboardComponent } from '../dashboard/dashboard.component';

@Component({
  selector: 'app-welcome',
  standalone: true,
  imports: [
    RelojComponent,
    DashboardComponent
  ],
  templateUrl: './welcome.component.html',
  styleUrls: ['./welcome.component.css']
})

export class WelcomeComponent {
  userName: string = JSON.parse(sessionStorage.getItem('userData') || "")?.user?.username;;
}
