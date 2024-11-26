import { Injectable } from '@angular/core';
import { UserService } from './users.service';
import { ModuloService } from './modulo.service';

@Injectable({
    providedIn: 'root',
})

export class PermiseService {
    constructor(private userService: UserService, private module: ModuloService) { }
    checkPermise(roleAccess: string | string[]) {
        if (!roleAccess) return true
        if (!this.userService.currentUser) return false;
        if (typeof roleAccess === "object") { for (const role of roleAccess) { if (this.xfind(role)) { return true } } }
        else { if (this.xfind(roleAccess)) return true }
        if (this.xfind("develop")) return true
        return false
    }

    xfind(role: string) {
        const i1 = this.module.cacheTable["zcpermise"]?.data.rows.find((a: any) => a.permise === role)
        if (i1) {
            const i2 = this.module.cacheTable["zcrolespermise"]?.data.rows.find((a: any) => a.idpermise === i1.idpermise && Boolean(this.userService.currentUser.roles.find((b: any) => b.id === a.idroles)))
            if (i2) return true
        }
        return false
    }

    getFormula(roleAccess: string | string[]) {
        return this.module.cacheTable["zcpermise"]?.data.rows.filter((a: any) => a.permise === roleAccess).map((a: any) => a.formula)
    }
}
