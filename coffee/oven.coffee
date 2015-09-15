# Hide loading spinner
# NProgress.configure { showSpinner: false }

class Oven
  constructor: () ->
    @dummyFill = []
    @actualDevices = {}
    @loadActualLoadedDevices()
    # @loadConfig()
  loadActualLoadedDevices:()->
    ajax = $.getJSON 'php/getActualDevicesFromOven.php'
    ajax.done (data)=>
      @dummyFill.push '' for dev in [(43 - data.length)..1]
      console.log data.length
      @actualDevices = data
      r.set 'dummyFill', @dummyFill
      r.set 'devices', @actualDevices
      r.set 'loading', false
    ajax.fail ()->
      r.set {
        error:
          status:true
          text:'Fallo al cargar los carrier de la base de datos'
          header:'Falla de base de datos'
        loading:false
      }
  loadConfig:()->
    # Descargar la informacion de configuracion.
  ingresar:(carrier)->
    if r.data.user.name isnt ''
      ajax = $.getJSON 'php/serverCommander.php', {
        action:'ingresar'
        carrier:carrier
        empleado:r.data.user.name
      }
      ajax.done (data)=>
        console.log data
        console.log carrier
      ajax.fail (data)->
        # console.log data
    else
      throw "No se especifico el nombre de usuario"
  sacar:(carrier, index)->
    if r.data.user.name isnt ''
      ajax = $.getJSON 'php/serverCommander.php', {
        action:'sacar'
        carrier:carrier
        empleado:r.data.user.name
      }
      ajax.done (data)=>
        @.actualDevices.splice index, 1
        console.log data
      ajax.fail (data)=>
        r.set 'error', {
          status:true
          text:"No se pudo sacar la demanda #{carrier}"
          header:"Error de base de datos"
        }
        window.setTimeout ()->
          r.set 'error.status', false
        , 10000
        throw "Fallo el sacar la demanda de la base de datos"
    else
      throw "No se especifico el nombre de usuario"
  getAllOutFromOven:()->
    r.set 'loading', false
    carrier = {}
    try
      while @actualDevices.length
        carrier = @actualDevices.shift()
        @sacar carrier.CARRIER, 0
        # console.log carrier
    catch e
      @actualDevices.unshift carrier
      r.set {
        error:
          status:true
          text:"#{e}"
          header:'Error'
        loading:false
      }
    @loadActualLoadedDevices()


class NextLoad
  constructor: () ->
    # load data from cache
    @items = []
    @reloadStoredInfo()
  reloadStoredInfo:()->
    ajax = $.getJSON 'php/serverCommander.php', action:'getNextLoadData'
    ajax.done (data)=>
      @items = data || []
      r.set 'nextLoad', @items
    ajax.fail (data)->
      console.log data
  validation:()->
    @validate item.carrier for item in @items
  validate:(carrier)->
    console.log carrier
  saveOntoServer:()->
    ajax = $.getJSON 'php/serverCommander.php', {
      action:'saveNextLoadData'
      data:@items
    }
    ajax.done (data)->
      # puedo notificar cuando exista informacion 
      # que ya esta guardada en la base de datos
      # y cuando exista informacion sin guardar
  addToOven:(index)->
    oven.ingresar @items[index].carrier
    @del index
  addAllToOven:()->
    carrier = {}
    try
      while @items.length
        carrier = @items.shift()
        oven.ingresar carrier.carrier
    catch e
      @items.unshift carrier
      r.set {
        error:
          status:true
          text:"#{e}"
          header:'Error'
        loading:false
      }
    finally
      @saveOntoServer()
      oven.loadActualLoadedDevices()
  add:(carrier)->
    @items.unshift {carrier:carrier}
    @saveOntoServer()
  del:(index)->
    @items.splice index, 1
    @saveOntoServer()


class Progressbar
  constructor: () ->
    @show = false
    @status = 0
    @active = false
    @do()
  do:()->
    r.set 'progressbar', 
      show:@show
      status:@status
      active:@active
  show:()->
    @show = true
    @do()
  hide:()->
    @show = false
    @do()
  start:()->
    @show = true
    @status = 3
    @active = true
    @do()
    @timer = setInterval ()=>
      @status++
      @do()
    ,1500
  complete:()->
    @status = 100
    clearInterval @timer
    @do()
    setTimeout ()=>
      @show = false
      @do()
    ,1500

  


###########################
# Objeto Ractive   ########
r = new Ractive {
  el: '#output',
  template: "#template",
  data:{
    loading:true
    nuevoCarrier:''
    user:
      name:''
    error:{
      status:false # true para mostrar el error
      text:''      # Lo que dice el error
      header:''    # El titulo del error
    }
    progressbar:{
      show:false
      status:0
    }
    time:(secs)->
      # console.log "'#{secs}'"
      # console.log  (new Date parseInt secs,10).toISOString()
  }
}
###########################

oven = new Oven()
next = new NextLoad()
progress = new Progressbar()

r.on 
  addToNext: (event)->
    event.original.preventDefault()
    # Si el dato que queremos insertar es un numero de 9 digitos
    if r.data.insertable is true
      next.add event.context.nuevoCarrier
      r.set 'nuevoCarrier', '' # limpiamos el campo
    # Siempre tenemos que parar el evento o nos mandara a otra pagina
  getOutOfOven: (event)->
    oven.sacar event.context.CARRIER, event.keypath.match(/\d/i)[0]
    event.original.preventDefault()
  deleteFromBuffer:(e)->
    e.original.preventDefault()
    next.del e.keypath.match(/\d/i)[0]
  addToOven:(e)->
    e.original.preventDefault()
    try
      next.addToOven e.keypath.match(/\d/i)[0]
    catch e
      console.log "#{e}"
  dismisError:(e)->
    e.original.preventDefault()
    r.set 'error.status', false
  addAllToOven:(e)->
    e.original.preventDefault();
    try
      next.addAllToOven()
    catch e
      console.log "#{e}"
  unloadOven:(e)->
    e.original.preventDefault()
    oven.getAllOutFromOven()
    


r.observe 'nuevoCarrier', (carrier) ->
  # Si son 9 numeros
  if (''+carrier).length == 9 and typeof carrier == "number"
    exists = _.find r.data.nextLoad, (el)->parseInt(el.carrier,10) is parseInt(r.data.nuevoCarrier,10)
    if typeof exists is 'undefined'
      r.set 'insertable', true
  else
    r.set('insertable', false)