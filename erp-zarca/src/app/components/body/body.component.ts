import { CommonModule, NgClass } from '@angular/common';
import { Component, Input, OnInit } from '@angular/core';
import { NavigationEnd, Router, RouterOutlet } from '@angular/router';
import { UserService } from '../../services/users.service';
import { ToastrService } from 'ngx-toastr';
import { ModuloService } from '../../services/modulo.service';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-body',
  standalone: true,
  imports: [NgClass, RouterOutlet, CommonModule, FormsModule,],
  templateUrl: './body.component.html',
  styleUrls: ['./body.component.css'],
})
export class BodyComponent implements OnInit {
  constructor(
    public userService: UserService,
    private router: Router,
    private toastr: ToastrService, private module: ModuloService
  ) { }
  render: boolean = false
  async ngOnInit() {
    const sessionUser = sessionStorage.getItem('userData');
    if (sessionUser) {
      this.userService.setUser(JSON.parse(sessionUser));
      await this.module.getModuloTable("zcpermise")
      await this.module.getModuloTable("zcrolespermise")
    }
    this.router.events.subscribe(async (event) => {
      if (event instanceof NavigationEnd) {
        if (!this.userService.isLoggedIn() && this.router.url !== '/login') {
          this.router.navigate(['/login']);
          if (this.router.url !== '/')
            this.toastr.warning('Necesitas iniciar sesión');
        }
      }
    });
    setTimeout(() => this.render = true, 0);
  }

  @Input() collapsed = false;

  @Input() screenWidth = 0;

  getBodyClass(): string {
    let styleClass = '';
    if (!this.collapsed && this.screenWidth > 768) {
      styleClass = 'body-trimmed';
    } else if (
      this.collapsed &&
      this.screenWidth <= 768 &&
      this.screenWidth > 0
    ) {
      styleClass = 'body-md-screen';
    }

    return styleClass;
  }
}
