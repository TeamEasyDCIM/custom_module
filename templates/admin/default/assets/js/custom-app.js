function CustomModuleInitialize()
{
    window.custommodule = new CustomModule();
    window.custommodule.initialize(document);
}

$(function()
{
    CustomModuleInitialize();
});

CustomModule = function() {};
CustomModule.prototype.initialize = function(context)
{
    console.log('Custom Module JS');
};