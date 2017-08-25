using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.conf
{
    public class MsSqlConfiguration : AbstractSqlConfiguration
    {
        public MsSqlConfiguration() : base() { }

        public MsSqlConfiguration(string connectionString) : base(connectionString) { }

        public MsSqlConfiguration(Dictionary<string, string> settings) : base(settings) { }

        public override string buildConnectionString()
        {
            StringBuilder sb = new StringBuilder();
            sb.Append("Data Source=");
            sb.Append(Hostname);
            sb.Append(";Initial Catalog=");
            sb.Append(Database);
            sb.Append(";User Id=");
            sb.Append(Username);
            sb.Append(";Password=");
            sb.Append(Password);
            return sb.ToString();
        }

    }
}
