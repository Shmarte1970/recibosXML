import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { SidenavComponent } from './components/sidenav/sidenav.component';
import { BodyComponent } from './components/body/body.component';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, SidenavComponent, BodyComponent, CommonModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css',
})

export class AppComponent {
  title: string = 'frontend-zarca';

  isSideNavCollapsed: boolean = false;
  screenWidth: number = 0;

  onToggleSidenav(data: SideNavToggle): void {
    this.screenWidth = data.screenWidth;
    this.isSideNavCollapsed = data.isCollapsed;
  }

}

interface SideNavToggle {
  screenWidth: number;
  isCollapsed: boolean;
}
