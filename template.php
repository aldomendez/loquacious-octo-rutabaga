
<!-- Menu principal -->
<div class="column">
<div class="ui  menu hdn">
  <a class="item" href="../">
    <i class="home icon"></i> Avago Tech
  </a>
  <a class="active item">
    Cargador del horno 
  </a>
  <a class="item" on-click="addAllToOven">
    Cargar horno ({{nextLoad.length}} demandas)
  </a>
  <a class="item" on-click="unloadOven">
    Descargar el horno
  </a>
   
<div class="right menu">
  <div class="item">
    <div class="ui icon input">
      <input type="text"  placeholder="Nombre del usuario" value="{{user.name}}">
      <i class="meh link icon"></i>
    </div>
  </div>
</div>

</div>
<!-- Menu de entrada de material -->

<div class="ui segment hdn">
  <div class="ui {{loading?'loading':''}} {{error.status?'error':''}} form">
  <div class="field">
    <label>Numero de carrier para ingresar al horno</label>
    <form class="ui input " on-submit="addToNext">
      <input type="text" id="nuevoCarrier" value="{{nuevoCarrier}}">
      {{#progressbar.show}}
      <div class="ui {{progressbar.active ? 'active':''}} red bottom attached progress">
        <div class="bar" style="width: {{progressbar.status}}%;"></div>
      </div>
      {{/progressbar.show}}
      <div class="ui {{insertable?'green':''}} corner label">
        <i class="{{insertable?'checkmark':'asterisk'}} icon"></i>
      </div>
    </form>
  </div>
  <!-- Error Message -->
  <div class="ui error message">
    <div class="header">{{error.header}}</div>
    <p>{{error.text}}</p>
    <p><a href="#" on-click="dismisError">Enterado</a></p>
  </div>
</div>
</div>

<!-- Lista de demandas cargadas -->
<div class="ui horizontal divider hdn">
  {{nextLoad.length}} 
</div>
<h4 class="header hdn">
  Demandas listas para ingresar
</h4>
<div class="ui five items hdn">
{{#nextLoad:elem}}
  <div class="item">
    <!-- label -->
    <a class="ui right green corner label">
      <i class="asterisk icon"></i>
    </a>
    <!-- /label -->
    <div class="content">
      <div class="meta">{{.PRODUCTO}}</div>
      <div class="name">{{.carrier}}</div>
      <p class="description"><a href="#" on-click="addToOven">ingresar</a> - <a href="#" on-click="deleteFromBuffer">borrar</a></p>
      <div class="ui small bottom right attached label">{{elem + 1}}</div>
    </div>
  </div>{{/nextLoad}}
</div>

<div class="ui horizontal divider hdn">
  {{devices.length}} 
</div>

<!-- Lista de demandas cargadas -->
<h4 class="header hdn">
  Demandas cargadas en el horno
</h4>
<div class="ui five items hdn">{{#devices}}
  <div class="item">
    <a class="star ui corner label" on-click="getOutOfOven" href="#">
      <i class="sign out icon"></i>
    </a>
  <!-- <div class="ui ribbon red label">Error</div> -->
    <div class="content">

      <div class="meta">{{ time(.SSS) }}</div>
      <div class="name">{{.CARRIER}}</div>
      <p class="description">{{.EMPLEADO}} - {{.FECHA}} {{.HORA}}</p>
    </div>
    <div class="extra">
    {{Math.ceil((.MINUTOS)/60)}} horas
    </div>
  </div>{{/devices}}
</div>

<!-- Empieza el formato para imprimir -->
<h4 class="hide">Formato de entrada y salida de material para hornos</h4>
<table class="ui compact small celled table segment hide">
  <thead class="inverted">
    <tr>
      <th colspan="9" class="t-head">Datos para meter al horno</th>
      <th colspan="4" class="t-head">Datos para sacar el material del horno</th>
    </tr>
    <tr>
      <th>Operador</th>
      <th>programa</th>
      <th>Horno</th>
      <th>Codigo</th>
      <th>No. piezas</th>
      <th>Fecha</th>
      <th>Hora</th>
      <th>Ciclo en que se va a meter</th>
      <th>fecha tentativa en la que se va a sacar</th>
      <th>Operador</th>
      <th>Fecha</th>
      <th>Programa Correcto</th>
      <th>Comentario/No.TML</th>
    </tr>
  </thead>
  <tbody>{{#devices}}
    <tr>
      <th>{{.EMPLEADO}}</th>
      <th></th>
      <th>FTC</th>
      <th>LR4</th>
      <th>N/A</th>
      <th>{{.FECHA}}</th>
      <th>{{.HORA}}</th>
      <th>0</th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th>{{.CARRIER}}</th>
    </tr>{{/devices}}
    {{#dummyFill}}
    <tr>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
    </tr>{{/dummyFill}}
  </tbody>
</table>
<p class="qms hide">QMS-F615.02</p>