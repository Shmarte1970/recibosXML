import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ModuloService } from '../../services/modulo.service';
import { ComponenteLoadingComponent } from '../componente/loading/loading.component';
import { AngularMaterialModule } from '../../module/app.angular.material.component';
import { FormBuilder, FormGroup, FormsModule, Validators } from '@angular/forms';
import { ComponentFormularioComponent } from './modal/formulario.component';
import { ToastrService } from 'ngx-toastr';
import { zcAccion } from './interface';
import { PermiseService } from '../../services/permise.service';
import { RelojComponent } from '../reloj/reloj.component';

@Component({
  selector: 'app-empresas',
  standalone: true,
  imports: [
    CommonModule,
    ComponenteLoadingComponent,
    AngularMaterialModule,
    FormsModule,
    ComponentFormularioComponent,
    RelojComponent
  ],
  templateUrl: './empresas.component.html',
  styleUrls: ['./empresas.component.css', '../../../styles.css']
})

export class EmpresasComponent implements OnInit {

  userName: string = JSON.parse(sessionStorage.getItem('userData') || "")?.user?.username;;


  constructor(
    public module: ModuloService,
    private formBuilder: FormBuilder,
    private toastr: ToastrService,
    public permise: PermiseService
  ) { }

  Columns: { Key: string, Name: string }[] =
    [{ Key: "idEmpresa", Name: "id", },
    { Key: "codigEmpresa", Name: "Código" },
    { Key: "nomEmpresa", Name: "Nombre" },
    { Key: "cifEmpresa", Name: "CIF", },
    { Key: "emailEmpresa", Name: "Email", },
    { Key: "telefono", Name: "Telefono", },]

  EmpresaForm!: FormGroup

  Accion: zcAccion = "Crear"
  Name: string = "Empresa"
  cacheList: string[] = [
    "zcempresa", "countries", "provinces", "cities", "postcodes", "zcdelegacion",
    "zccontactos", "zccomercial", "zcempresazcdelegacion",
    "zcdelegacionzccontacto", "zcdelegacionzccomercial", "zcurbana", "zccargo", "zcempresapagoscobro",
    "zcformasdepago", "zcctabancariaszarca", "zcvencimiento",
  ]

  dialog: boolean = false
  ngOnInit(): void {
    this.module.loading = true
    this.getData();
    this.EmpresaForm = this.formBuilder.group({
      "idEmpresa": this.formBuilder.control(""),
      "codigEmpresa": this.formBuilder.control(""),
      "nomEmpresa": this.formBuilder.control("", Validators.required),
      "aliasEmpresa": this.formBuilder.control(""),
      "cifEmpresa": this.formBuilder.control(""),//los validatores esta en el campo de Form
      "zcurbanaIdurbana": this.formBuilder.control("", Validators.required),
      "direccionFiscal": this.formBuilder.control("", Validators.required),
      "postcodeId": this.formBuilder.control("", Validators.required),
      "cityId": this.formBuilder.control("", Validators.required),
      "provinceId": this.formBuilder.control("", Validators.required),
      "countryId": this.formBuilder.control("", Validators.required),
      "emailEmpresa": this.formBuilder.control("", Validators.required),
      "telefono": this.formBuilder.control("", Validators.required),
      "telefono2": this.formBuilder.control(""),
      "moroso": this.formBuilder.control(false),
      "cliente": this.formBuilder.control(""),
      "proveedor": this.formBuilder.control(""),
      "transportista": this.formBuilder.control(""),
      "potencial": this.formBuilder.control(""),
      "horarioEmpresa": this.formBuilder.control(""),
      "url": this.formBuilder.control(""),
      "observaciones": this.formBuilder.control(""),
      "envioFacturas": this.formBuilder.control(true),
      "enable": this.formBuilder.control(1),
      "userId": this.formBuilder.control(""),
      "nombreBanco": this.formBuilder.control(""),
      "cuentaBancaria": this.formBuilder.control(""),
      "facturaElectronica": this.formBuilder.control(0),
      "idctaZarca": this.formBuilder.control(""),
      "idCtaBcoCompra": this.formBuilder.control(""),
      "idCtaBcoVenta": this.formBuilder.control(""),
      "idCtaBcoAlquiler": this.formBuilder.control(""),
      "urbana": this.formBuilder.control(""),
      "cpEmpresa": this.formBuilder.control(""),
      "poblacionEmpresa": this.formBuilder.control(""),
      "provinciaEmpresa": this.formBuilder.control(""),
      "paisEmpresa": this.formBuilder.control(""),
    },);
  }

  async getData() {
    setTimeout(async () => {
      const total = this.cacheList.length - 1
      let progress = 0
      await new Promise<void>((resolve) => {
        for (const table of this.cacheList) this.module.getModuloTable(table).finally(() => progress === total ? resolve() : progress++)
      })
      if (this.permise.checkPermise(['view_filter_empresa']) && !this.permise.checkPermise(['view_all_empresa', 'view_empresa'])) {
        const formula = this.permise.getFormula("view_filter_empresa")
        this.module.cacheTable["zcempresa"].data.rows = this.module.cacheTable["zcempresa"].data.rows.filter((a: any) =>
          this.module.cacheTable["zcempresazcdelegacion"].data.rows.filter(
            (a: any) =>
              this.module.cacheTable["zcdelegacionzccomercial"].data.rows.filter(
                (a: any) =>
                  this.module.cacheTable["zccomercial"].data.rows.filter(
                    (a: any) => formula.includes(a.userId?.toString())
                  ).map((a: any) => a.idcomercial).includes(a.idzccomercial)
              ).map((a: any) => a.idzcDelegacion).includes(a.idzcDelegacion)
          ).map((a: any) => a.idzcEmpresa).includes(a.idEmpresa)
        )
      }
      this.pDatas = JSON.parse(JSON.stringify(this.module.cacheTable[this.cacheList[0]]))
      this.module.loading = false
    }, 0);
  }

  OpenModal() {
    if (this.Accion === "Editar") this.EmpresaForm.reset()
    this.openModal = true
  }
  DoModal() {
    this.module.loading = true
    setTimeout(async () => {
      try {
        if (this.Accion === "Activar") {
          this.sData.enable = !this.sData.enable
          this.EmpresaForm.patchValue(this.sData)
          await this.module.ModuloTableUpdateElement(this.cacheList[0], this.EmpresaForm.getRawValue())
          await this.module.UpdateModuleTable(this.cacheList[0])
        } else if (this.Accion === "Crear") {
          this.EmpresaForm.patchValue({ enable: 1 })
          if (this.EmpresaForm.status === "VALID") {
            const data = await this.module.ModuloTableCreateElement(this.cacheList[0], this.EmpresaForm.getRawValue())
            this.EmpresaForm.patchValue({ codigEmpresa: "ZC" + data.idEmpresa, idEmpresa: data.idEmpresa })
            const data2 = await this.module.ModuloTableUpdateElement(this.cacheList[0], this.EmpresaForm.getRawValue())
            await this.module.UpdateModuleTable("zcdelegacion")
            await this.module.UpdateModuleTable("zcempresazcdelegacion")
            await this.module.UpdateModuleTable(this.cacheList[0])
            this.toastr.success(`Acción realizado correctamente!`);
            this.sData = data2
            this.EmpresaForm.patchValue(data2)
            this.openModal = false
            this.dialog = true
          } else this.EmpresaForm.markAllAsTouched()
        } else if (this.Accion === "Editar") {
          if (this.EmpresaForm.status === "VALID") {
            await this.module.ModuloTableUpdateElement(this.cacheList[0], this.EmpresaForm.getRawValue())
            await this.module.UpdateModuleTable(this.cacheList[0])
            this.toastr.success(`Acción realizado correctamente!`);
            this.openModal = false
          } else this.EmpresaForm.markAllAsTouched()
        }
      } catch (e) { }
      this.pDatas = JSON.parse(JSON.stringify(this.module.cacheTable[this.cacheList[0]]))
      this.Search()
      this.module.loading = false
    }, 0);
  }

  Buscar!: string
  Paginador: [number, number] = [0, 15]
  sData!: any
  pDatas!: any
  openModal: boolean = false

  Search() {
    const data = this.module.cacheTable[this.cacheList[0]];
    this.pDatas.data.rows = this.Buscar ?
      data.data.rows.filter((a: any) =>
        (a.nomEmpresa + a.cifEmpresa + a.aliasEmpresa + a.telefono + "x").toUpperCase().startsWith(this.Buscar.toUpperCase())
      ) : data.data.rows;
    this.Paginador[1] = 10;
  }

  onScroll(event: any): void {
    if ((event.target.offsetHeight + event.target.scrollTop) >= event.target.scrollHeight)
      this.Paginador[1] = this.Paginador[1] + 10 > this.pDatas.data.count ? this.pDatas.data.count : this.Paginador[1] + 10
  }
}

