using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.conf
{
    public class OracleConfiguration : AbstractSqlConfiguration
    {
        public OracleConfiguration(string connectionString) : base(connectionString) { }

        public OracleConfiguration(Dictionary<string, string> settings) : base(settings) 
        {
            /* Need to add the rest of the properties after base(settings) is invoked */
        }

        public override string buildConnectionString()
        {
            throw new NotImplementedException("Not yet implemented");
            /* This isn't right - needs to be built better
             * 
            StringBuilder sb = new StringBuilder();
            sb.Append("Data Source=(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=");
            sb.Append(Hostname);
            sb.Append(")(PORT=");
            sb.Append(Port.ToString());
            sb.Append("))(CONNECT_DATA=SERVICE_NAME=");
            sb.Append(ServiceName);
            sb.Append(")));User ID=");
            sb.Append(Username);
            sb.Append(";Password=");
            sb.Append(Password);
            sb.Append(";");
            return sb.ToString();
             */
        }

        public string ServiceName { get; set; }
    }
}
