using System;
using System.Collections.Generic;
using System.Text;
using System.Net;
using System.Net.Sockets;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaDirectConnectStrategy : IConnectStrategy
    {
        const int LISTENER_TIMEOUT = 30000;
        // Needs to be VistaConnection to share the connection's socket and port
        VistaConnection cxn;

        DataSource dataSource;

        public VistaDirectConnectStrategy(AbstractConnection cxn)
        {
            this.cxn = (VistaConnection)cxn;
            this.dataSource = cxn.DataSource;
        }

        public void connect()
        {
            cxn.IsConnected = false;

            if (dataSource == null)
            {
                throw new ArgumentNullException("No data source");
            }
            string hostname = dataSource.Provider;
            if (hostname == null || hostname == "")
            {
                throw new ArgumentNullException("No provider (hostname)");
            }

            //Start my listener - make sure we use an IPV4 address as IPV6 address listeners are incompatible with this algorithm
            IPHostEntry hostEntry = Dns.GetHostEntry("localhost");
            IPAddress[] myIPs = ((IPHostEntry)Dns.GetHostEntry(hostEntry.HostName)).AddressList;
            IPAddress myIP = null;
            foreach (IPAddress ip in myIPs)
            {
                if (ip != null && ip.AddressFamily == AddressFamily.InterNetwork)
                {
                    myIP = ip;
                    break;
                }
            }
            if (myIP == null)
            {
                throw new Exception("Unable to obtain a local IPV4 address for the connection listener");
            }
            IPEndPoint myEndPoint = new IPEndPoint(myIP, 0);
            MdoSocket listener = new MdoSocket(AddressFamily.InterNetwork, SocketType.Stream, ProtocolType.Tcp);
            listener.Bind(myEndPoint);
            int myPort = ((IPEndPoint)listener.LocalEndPoint).Port;
            listener.Listen(2);

            //Build the request message
            string request = "TCPconnect^" + myIP.ToString() + '^' + myPort + '^' + hostEntry.HostName;
            request = "{XWB}" + StringUtils.strPack(request, 5);

            //Config my client socket and connnect to VistA
            IPAddress vistaIP = null;
            try
            {
                vistaIP = ((IPAddress[])Dns.GetHostAddresses(hostname))[0]; // GetHostAddresses takes a hostname or IP - cool!
            }
            catch (SocketException se)
            {
                throw new ConnectionException("No route to host " + hostname, se);
            }

            IPEndPoint vistaEndPoint = new IPEndPoint(vistaIP, cxn.port);
            cxn.socket = new Socket(AddressFamily.InterNetwork, SocketType.Stream, ProtocolType.Tcp);
            cxn.socket.SetSocketOption(SocketOptionLevel.Socket, SocketOptionName.ReceiveTimeout, cxn.ConnectTimeout);
            try
            {
                cxn.socket.Connect(vistaEndPoint);
            }
            catch (SocketException se)
            {
                throw new ConnectionException("No VistA listener at " + hostname + ", port " + cxn.port, se);
            }
            if (!cxn.socket.Connected)
            {
                listener.Close();
                throw new ConnectionException("Unable to connect to " + hostname + ", port " + cxn.port);
            }

            string reply = "";
            try
            {
                reply = (string)cxn.query(request);
            }
            catch (SocketException se)
            {
                throw new ConnectionException("No VistA listener at " + vistaIP, se);
            }
            if (reply != "accept")
            {
                listener.Close(cxn.ConnectTimeout);
                cxn.socket.Close();
                throw new ConnectionException("Unaccepted by " + hostname);
            }

            //Do the hookup
            cxn.socket.Close();
            cxn.socket = listener.Accept(LISTENER_TIMEOUT); // use MdoSocket timeout arg so listener doesn't turn in to a server in the event Vista fails to call back
            listener.Close();

            cxn.socket.SetSocketOption(SocketOptionLevel.Socket, SocketOptionName.ReceiveTimeout, cxn.ReadTimeout);

            // Fix up the return stuff for successful connection
            cxn.WelcomeMessage = cxn.getWelcomeMessage();
            cxn.IsConnected = true;
        }

        //internal bool isTestSystem()
        //{
        //    VistaQuery vq = new VistaQuery("XUS SIGNON SETUP");
        //    string response = (string)cxn.query(vq.buildMessage());
        //    string[] flds = StringUtils.split(response, StringUtils.CRLF);
        //    return flds[7] == "0";
        //}
    }
}
