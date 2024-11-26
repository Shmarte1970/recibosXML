import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { UserService } from './users.service';
import { Router } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { ToastrService } from 'ngx-toastr';

@Injectable({
  providedIn: 'root',
})
export class ModuloService {
  private readonly API_URL = 'http://localhost:8080/api/v1';
  //private readonly API_URL = 'http://192.168.0.16:8080/api/v1'; 
  constructor(
    private http: HttpClient,
    private user: UserService,
    private router: Router,
    private toastr: ToastrService
  ) { }

  Error(err: any) {
    if (err) {
      if (err.status === 401) this.router.navigate(['']);
      if (window.location.hostname === 'localhost') console.log(err)
      if (err.error.errors) {
        let text = ""
        for (const e of err.error.errors) text += "- " + e + "\n"
        this.toastr.error(`${text}`, "Se ha ocurrido lo siguiente: ");
      }
    }
    throw err
  }
  cacheTable: any = {}
  loading: boolean = false

  async getModuloTable(model: string) {
    if (!this.cacheTable[model]) {
      this.cacheTable[model] = await firstValueFrom(this.http.get<any>(`${this.API_URL}/modulo/${model}/`, this.user.options)).catch(err => this.Error(err));
      return this.cacheTable[model]
    } return true
  }
  async UpdateModuleTable(model: string) {
    this.cacheTable[model] = await firstValueFrom(this.http.get<any>(`${this.API_URL}/modulo/${model}/`, this.user.options)).catch(err => this.Error(err));
    return this.cacheTable[model]
  }
  async ModuloTableCreateElement(model: string, data: any) {
    return await firstValueFrom(this.http.post<any>(`${this.API_URL}/modulo/${model}/add/`, { data }, this.user.options)).catch(err => this.Error(err));
  }
  async ModuloTableUpdateElement(model: string, data: any) {
    return await firstValueFrom(this.http.post<any>(`${this.API_URL}/modulo/${model}/update/`, { data }, this.user.options)).catch(err => this.Error(err));
  }
  async ModuloTableDeleteElement(model: string, data: any) {
    return await firstValueFrom(this.http.post<any>(`${this.API_URL}/modulo/${model}/delete/`, { data }, this.user.options)).catch(err => this.Error(err));
  }
}
