using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.ServiceModel.Activation;
using System.ServiceModel;

namespace gov.va.medora.mdws
{
    [AspNetCompatibilityRequirements(RequirementsMode = AspNetCompatibilityRequirementsMode.Allowed)]
    [ServiceBehavior(InstanceContextMode = InstanceContextMode.Single)]
    public class RestEmrSvc : IRestEmrSvc
    {
    }

    public interface IRestEmrSvc
    {
    }
}