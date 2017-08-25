using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class DataSourceTO : AbstractTO
    {
        public String protocol = "";
        public String modality = "";
        public int timeout;
        public int port;
        public String provider = "";
        public String status = "";
        public String description = "";
        public String context = "";
        public bool testSource = false;
        public String vendor = "";
        public String version;
        public TaggedText siteId;
        public String welcomeMessage = "";

        public DataSourceTO() { }

        public DataSourceTO(DataSource mdoSrc)
        {
            this.protocol = mdoSrc.Protocol == null ? "" : mdoSrc.Protocol;
            this.modality = mdoSrc.Modality == null ? "" : mdoSrc.Modality;
            //this.timeout = mdoSrc.Timeout;
            this.port = mdoSrc.Port;
            this.provider = mdoSrc.Provider == null ? "" : mdoSrc.Provider;
            this.status = mdoSrc.Status == null ? "" : mdoSrc.Status;
            this.description = mdoSrc.Description == null ? "" : mdoSrc.Description;
            this.context = mdoSrc.Context == null ? "" : mdoSrc.Context;
            this.testSource = mdoSrc.IsTestSource;
            this.vendor = mdoSrc.Vendor == null ? "" : mdoSrc.Vendor;
            this.version = mdoSrc.Version == null ? "" : mdoSrc.Version;
            this.siteId = new TaggedText(mdoSrc.SiteId.Id, mdoSrc.SiteId.Name);
        }
    }
}
