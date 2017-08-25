using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws
{
    public abstract class QueryTemplate
    {
        public IList<String> QuerySites { get; set; }

        public abstract void setUpQuery(MySession session);

        public abstract object query(Delegate theMethod, object[] methodArgs);

        public abstract void tearDownQuery(MySession session);

        public virtual object execute(MySession session, Delegate theMethod, object[] methodArgs)
        {
            setUpQuery(session);
            object result = null;
            try
            {
                result = query(theMethod, methodArgs);
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                tearDownQuery(session);
            }
            return result;
        }

        public static QueryTemplate getQuery(QueryType type)
        {
            if (QueryType.REST == type)
            {
                return new RestQuery();
            }
            else if (QueryType.SOAP == type)
            {
                return new SoapQuery();
            }
            else if (QueryType.STATELESS == type)
            {
                return new StatelessQuery();
            }
            else
            {
                throw new NotImplementedException();
            }
        }

        public static QueryTemplate getQuery(QueryType type, String sites)
        {
            QueryTemplate qt = getQuery(type);
            qt.QuerySites = new List<String>() { sites };
            return qt;
        }
    }

    public enum QueryType
    {
        REST,
        SOAP,
        STATELESS
    }
}