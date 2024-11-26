import { Component } from '@angular/core';
import { RelojComponent } from '../reloj/reloj.component';


@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [
    RelojComponent
  ],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.css'
})
export class DashboardComponent {

}
