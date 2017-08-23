using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using NHapi.Model.V24.Segment;
using NHapi.Base.Model;

namespace gov.va.medora.mdo.dao.hl7.rxRefill
{
    public class OMP_O09_PID : NHapi.Model.V24.Message.OMP_O09
    {
        public OMP_O09_PID()
            : base()
        {
            this.add(typeof(PID), true, false);
            this.add(typeof(ORC), true, true);
            this.add(typeof(RXE), true, true);
            //this.add(typeof(OMP_O09_ORCRXE), true, true);
        }

        //public OMP_O09_ORCRXE getOrcrxe(int rep)
        //{
        //    return (OMP_O09_ORCRXE)this.GetStructure("OMP_O09_ORCRXE", rep);
        //}

        public ORC getOrc(int rep)
        {
            return (ORC)this.GetStructure("ORC", rep);
        }

        public RXE getRxe(int rep)
        {
            return (RXE)this.GetStructure("RXE", rep);
        }

        public PID getPid()
        {
            return (PID)this.GetStructure("PID");
        }

        public string encode()
        {
            return "";
        }
    }

    //public class OMP_O09_ORCRXE : AbstractGroup
    //{
    //    public OMP_O09_ORCRXE() : base(new NHapi.Base.Parser.DefaultModelClassFactory())
    //    {
    //        this.add(typeof(ORC), true, false);
    //        this.add(typeof(RXE), true, false);
    //    }

    //    public ORC getOrc()
    //    {
    //        return (ORC)this.GetStructure("ORC");
    //    }

    //    public RXE getRxe()
    //    {
    //        return (RXE)this.GetStructure("RXE");
    //    }
    //}
}
