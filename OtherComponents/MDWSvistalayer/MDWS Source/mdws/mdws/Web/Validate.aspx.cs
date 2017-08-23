using System;
using System.IO;
using System.Text;
using System.Web.Caching;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.HtmlControls;
using gov.va.medora.mdws.bse;

namespace gov.va.medora.mdws.Web.bse
{

    public partial class Validate : System.Web.UI.Page
    {
        protected const string DATA_PREFIX = "xval=";
        protected int cacheSeconds = 60;

        protected void Page_Load(object sender, EventArgs e)
        {
            validate();
        }

        private void validate()
        {
            setupNoCachePolicy();
            if ("POST".CompareTo(Request.HttpMethod) != 0)
            {
                Response.StatusCode = 404;
                Response.End();
                return;
            }
            StreamReader reader = new StreamReader(Request.InputStream);
            string line = null;
            string result = null;
            string key = null;

            while ((line = reader.ReadLine()) != null)
            {
                try
                {
                    if (line.Length <= DATA_PREFIX.Length || !line.ToLower().StartsWith(DATA_PREFIX))
                    {
                        continue;
                    }
                    key = line.Substring(DATA_PREFIX.Length);
                    IPrincipal principal = null;
                    if (IsCaching())
                    {
                        principal = getPrincipalFromCache(key);
                        if (principal == null)
                        {
                            principal = getPrincipalFromSecurityProvider(key);
                            if (principal != null)
                            {
                                DateTime expireDateTime = DateTime.Now.AddSeconds(cacheSeconds);
                                try
                                {
                                    Cache.Add(key, principal, null, expireDateTime, TimeSpan.Zero, CacheItemPriority.Normal, null);
                                }
                                catch (Exception ex)
                                {
                                }
                            }
                        }
                    }
                    else
                    {
                        principal = getPrincipalFromSecurityProvider(key);
                    }
                    if (principal != null)
                    {
                        result = principal.Value;
                    }
                    else
                    {
                    }
                    break;
                }
                catch (Exception ex)
                {
                }
            }
            if (result == null)
            {
                Response.StatusCode = 404;
            }
            else
            {
                Response.Write(result);
                Response.End();
            }
        }

        public int CacheSeconds
        {
            set { cacheSeconds = value; }
        }

        protected void setupNoCachePolicy()
        {
            Response.CacheControl = "no-cache";
            Response.AddHeader("Pragma", "no-cache");
            Response.Expires = -1;
        }

        protected IPrincipal getPrincipalFromCache(string key)
        {
            try
            {
                IPrincipal prin = (IPrincipal)Cache[key];
                return prin;
            }
            catch (Exception e)
            {
            }
            return null;
        }

        protected IPrincipal getPrincipalFromSecurityProvider(string key)
        {
            try
            {
                IUserSecurityProvider securityProvider = new VistaUserSecurityProvider();
                IPrincipal prin = securityProvider.getUserPrincipal(key);
                return prin;
            }
            catch (Exception e)
            {
                Response.StatusCode = 404;
                Response.Write(e.Message + "\r\n" + e.StackTrace + "\r\n");
                Response.End();
            }
            return null;
        }

        protected virtual bool IsCaching()
        {
            return cacheSeconds > 0;
        }

        internal void errorExit()
        {
        }
    }
}
