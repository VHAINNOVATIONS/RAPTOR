using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws
{
    public class Service : IHttpHandler
    {
        public bool IsReusable
        {
            get { throw new NotImplementedException(); }
        }

        public void ProcessRequest(HttpContext context)
        {
            switch (context.Request.HttpMethod)
            {
                case "GET":
                    // read
                    break;
                case "POST":
                    // write
                    break;
                case "PUT":
                    // update
                    break;
                case "DELETE":
                    // delete
                    break;
                default:
                    break;
            }
        }

    }
}